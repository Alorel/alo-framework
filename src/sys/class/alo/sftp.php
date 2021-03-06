<?php

    namespace Alo;

    use Alo\Exception\ExtensionException as EE;
    use Alo\Exception\FileSystemException as FE;
    use Alo\Exception\SFTPException as SE;
    use Alo\FileSystem\File;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * SFTP handler
         *
         * @author Arturas Molcanovas <a.molcanovas@gmail.com>
         */
        class SFTP {

            /**
             * Defines the sort direction as ascending
             *
             * @var int
             */
            const SORT_ASC = SCANDIR_SORT_ASCENDING;
            /**
             * Defines the sort direction as descending
             *
             * @var int
             */
            const SORT_DESC = SCANDIR_SORT_DESCENDING;
            /**
             * Defines a parameter as "retry count"
             *
             * @var int
             */
            const P_RETRY_COUNT = 101;
            /**
             * Defines a parameter as "retry wait time"
             *
             * @var int
             */
            const P_RETRY_TIME = 102;
            /**
             * Static reference to the last instance of the class
             *
             * @var SFTP
             */
            static $this;
            /**
             * The endpoint URL
             *
             * @var string
             */
            protected $url;
            /**
             * The SFTP username
             *
             * @var string
             */
            protected $user;
            /**
             * Path to the public authentication key
             *
             * @var string
             */
            protected $pubkey;
            /**
             * Path to the private authentication key
             *
             * @var string
             */
            protected $privkey;
            /**
             * Private authentication key password
             *
             * @var string
             */
            protected $pw;
            /**
             * Directory in use
             *
             * @var string
             */
            protected $dir;
            /**
             * The SSH2 connection
             *
             * @var resource
             */
            protected $connection;
            /**
             * The SFTP subsystem
             *
             * @var resource
             */
            protected $sftp;
            /**
             * The local directory set
             *
             * @var string
             */
            protected $localDir;
            /**
             * Maximum amount of retries for an operation
             *
             * @var int
             */
            protected $retryCountMax;

            /**
             * Time in seconds between retries
             *
             * @var int
             */
            protected $retryTime;

            /**
             * Instantiates the library
             *
             * @param array $params Optional parameters - see class P_* constants
             *
             * @see self::P_RETRY_COUNT
             * @see self::P_RETRY_TIME
             * @throws EE When the SSH2 extension is not loaded
             */
            function __construct($params = []) {
                if (!function_exists('ssh2_connect')) {
                    throw new EE('SSH2 extension not loaded', EE::E_EXT_NOT_LOADED);
                } else {
                    $this->dir = DIR_INDEX;

                    $this->retryCountMax = (int)\get($params[self::P_RETRY_COUNT]);
                    $this->retryTime     = (int)\get($params[self::P_RETRY_TIME]) ? $params[self::P_RETRY_TIME] : 3;

                    \Log::debug('SSH2 class initialised');
                }

                self::$this = &$this;
            }

            /**
             * Instantiates the library
             *
             * @param array $params Optional parameters - see class P_* constants
             *
             * @see self::P_RETRY_COUNT
             * @see self::P_RETRY_TIME
             * @throws EE When the SSH2 extension is not loaded
             *
             * @return SFTP
             */
            static function sftp($params = []) {
                return new SFTP($params);
            }

            /**
             * If no parameter is passed gets the maximum amount of retry attempts for failed operations, otherwise sets it.
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param int $int The amount
             *
             * @return boolean|int
             */
            function retryCount($int = -1) {
                if ($int === -1) {
                    return $this->retryCountMax;
                } elseif (is_numeric($int) && $int >= 0) {
                    $this->retryCountMax = (int)$int;
                    \Log::debug('Retry count set to ' . $int);

                    return true;
                } else {
                    return false;
                }
            }

            /**
             * If no parameter is passed gets the time to wait between operation retries, otherwise sets it
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param int $int The time in seconds
             *
             * @return boolean
             */
            function retryTime($int = -1) {
                if ($int === -1) {
                    return $this->retryTime;
                } elseif (is_numeric($int) && $int > 0) {
                    $this->retryTime = (int)$int;
                    \Log::debug('Retry time set to ' . $int);

                    return true;
                } else {
                    return false;
                }
            }

            /**
             * Sets the local directory
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $dir The directory path
             *
             * @return SFTP
             */
            function loc($dir) {
                $dir = rtrim($dir, ' ' . DIRECTORY_SEPARATOR);
                if (!$dir) {
                    $dir = DIRECTORY_SEPARATOR;
                }
                $this->localDir = $dir;
                \Log::debug('Local dir set to ' . $dir);

                return $this;
            }

            /**
             * Scans a directory for files and subdirectories
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param int $sortingOrder The sorting order
             *
             * @return array ["dirs" => [], "files" => []]
             */
            function scandir($sortingOrder = self::SORT_ASC) {
                $this->checkSubsystem();

                $dir = scandir('ssh2.sftp://' . $this->sftp . DIRECTORY_SEPARATOR . $this->dir, $sortingOrder);
                $r   = ['dirs' => [], 'files' => []];

                foreach ($dir as $v) {
                    //Ignore hidden
                    if ($v == '.' || $v == '..' || stripos($v, '.') === 0) {
                        continue;
                    } elseif (self::isFile($v)) {
                        $r['files'][] = $v;
                    } else {
                        $r['dirs'][] = $v;
                    }
                }

                return $r;
            }

            /**
             * Checks if the SFTP subsystem was initialised
             *
             * @author Art <a.molcanovas@gmail.com>
             * @throws SE When the connection ultimately fails
             * @return SFTP
             */
            function checkSubsystem() {
                if (!$this->sftp) {
                    \Log::debug('SFTP subsystem wasn\'t initialised when a dependant' .
                                ' method was called. Initialising.');
                    $this->connect();
                }

                return $this;
            }

            /**
             * Creates a SSH2 connection
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param int $attempt Current attempt number
             *
             * @return SFTP
             * @throws SE When the connection ultimately fails
             */
            function connect($attempt = 0) {
                if (!($this->connection = ssh2_connect($this->url))) {
                    $msg = 'Failed to initialise SSH2 connection';
                    $attempt++;

                    if ($attempt - 1 < $this->retryCountMax) {
                        \Log::error($msg . '. Retrying again' . ' in ' . $this->retryTime . ' seconds [' . $attempt .
                                    '/' . $this->retryCountMax . ']');

                        time_sleep_until(time() + $this->retryTime);

                        return $this->connect($attempt);
                    } else {
                        throw new SE($msg . ' after ' . $attempt . ' attempts', SE::E_CONNECT);
                    }
                } else {
                    \Log::debug('Initialised SSH2 connection');

                    return $this->auth();
                }
            }

            /**
             * Authenticates the SSH2 connection
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param int $attempt The current attempt # at authentication
             *
             * @return SFTP
             * @throws SE When authentication permanently fails
             */
            protected function auth($attempt = 0) {
                if (!ssh2_auth_pubkey_file($this->connection, $this->user, $this->pubkey, $this->privkey, $this->pw)) {
                    $msg = 'Failed to authenticate SSH2 connection';
                    ++$attempt;

                    if ($attempt - 1 < $this->retryCountMax) {
                        \Log::error($msg . '. Retrying in ' . $this->retryTime . ' seconds [' . $attempt . '/' .
                                    $this->retryCountMax . ']');
                        time_sleep_until(time() + $this->retryTime);

                        return $this->auth($attempt);
                    } else {
                        throw new SE($msg . ' after ' . $attempt . ' retries', SE::E_AUTH);
                    }
                } else {
                    \Log::debug('SSH2 connection authenticated');

                    return $this->ssh2Sftp();
                }
            }

            /**
             * Initialises the SFTP subsystem
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param int $attempt Current retry number
             *
             * @return SFTP
             * @throws SE When initialising the SFTP system permanently fails
             */
            protected function ssh2Sftp($attempt = 0) {
                if ($this->sftp = ssh2_sftp($this->connection)) {
                    \Log::debug('Initialised SFTP subsystem');

                    return $this;
                } else {
                    $msg = 'Failed to initialise SFTP subsystem';
                    ++$attempt;

                    if ($attempt - 1 < $this->retryCountMax) {
                        \Log::error($msg . '. Retrying again' . ' in ' . $this->retryTime . ' seconds [' . $attempt .
                                    '/' . $this->retryCountMax . ']');
                        time_sleep_until(time() + $this->retryTime);

                        return $this->ssh2Sftp($attempt);
                    } else {
                        throw new SE($msg . ' after ' . $attempt . ' attempts', SE::E_SUBSYSTEM);
                    }
                }
            }

            /**
             * Checks whether a resource is a file or directory based on whether it has a file extension
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $resource The resource name to check
             *
             * @return boolean
             */
            protected static function isFile($resource) {
                $pos = stripos($resource, '.');

                return $pos !== false && $pos !== 0;
            }

            /**
             * Downloads a file to $this->localDir
             *
             * @author Art <a.molcanovas@gmail.com>
             * @see    self::$localDir
             *
             * @param string $file Remote file name
             *
             * @throws SE When the file cannot be fetched
             * @throws FE When the name is invalid
             * @return SFTP
             */
            function downloadFile($file) {
                \Log::debug('Downloading file ' . $file . ' to ' . $this->localDir);
                $fetch = $this->getFileContents($file);

                $local = new File();
                $local->dir($this->localDir)->name($file);
                $local->content($fetch)->write();

                return $this;
            }

            /**
             * Gets the file contents
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $file File name
             *
             * @return string String representation of the file
             * @throws SE When the file cannot be fetched
             */
            function getFileContents($file) {
                $this->checkSubsystem();
                $remoteFile = $this->resolvePath($file);

                $file = file_get_contents('ssh2.sftp://' . $this->sftp . '/' . $remoteFile);
                if ($file === false) {
                    throw new SE('Failed to fetch file ' . $remoteFile, SE::E_FILE_NOT_FETCHED);
                } else {
                    return $file;
                }
            }

            /**
             * Modifies the path based on whether it's relative or absolute
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $item Item name
             *
             * @return string The resolved path
             */
            protected function resolvePath($item) {
                return (stripos($item, DIRECTORY_SEPARATOR) === 0) ? substr($item, 1) :
                    $this->dir . DIRECTORY_SEPARATOR . $item;
            }

            /**
             * Uploads a file to the SFTP folder from the local folder
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $file File name
             *
             * @return boolean
             * @throws SE When the local file cannot be read
             */
            function upload($file) {
                $this->checkSubsystem();
                $path = $this->localDir . DIRECTORY_SEPARATOR . $file;

                if (!$content = file_get_contents($path)) {
                    throw new SE('Local file ' . $path . ' cannot be read', SE::E_LOCAL_FILE_NOT_READ);
                } else {
                    \Log::debug('Uploading remote file ' . $file);

                    return $this->makeFile($file, $content);
                }
            }

            /**
             * Creates a file in the SFTP directory
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $file    File name
             * @param mixed  $content File content
             *
             * @return boolean
             * @throws SE When remote fopen fails
             */
            function makeFile($file, $content) {
                $this->checkSubsystem();
                $remoteFile = $this->resolvePath($file);

                if (!$fp = fopen('ssh2.sftp://' . $this->sftp . DIRECTORY_SEPARATOR . $remoteFile,
                                 File::M_WRITE_TRUNCATE_BEGIN)
                ) {
                    throw new SE('Failed to remotely fopen ' . $remoteFile, SE::E_FILE_CREATE_FAIL);
                } else {
                    flock($fp, LOCK_EX);
                    fwrite($fp, $content);
                    flock($fp, LOCK_UN);
                    fclose($fp);
                    \Log::debug('Wrote remote file ' . $remoteFile);

                    return true;
                }
            }

            /**
             * Deletes an item on the SFTP server
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $item File or directory name
             *
             * @return boolean
             */
            function delete($item) {
                $this->checkSubsystem();
                $path = $this->resolvePath($item);

                if (self::isFile($item)) {
                    $success = ssh2_sftp_unlink($this->sftp, $path);
                } else {
                    $success = ssh2_sftp_rmdir($this->sftp, $path);
                }

                if ($success) {
                    \Log::debug('Deleted ' . $item);

                    return true;
                } else {
                    \Log::error('Failed to delete ' . $item);

                    return false;
                }
            }

            /**
             * Returns a string representation of SFTP credentials
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return string
             */
            function __toString() {
                return 'User: ' . $this->user . '; PrivKey:' . $this->privkey . '; PubKey: ' . $this->pubkey .
                       '; Password hash: ' . get($this->pw) ? md5($this->pw) : 'NO HASH CONTENT SET';
            }

            /**
             * If no parameter is passed gets the SFTP server URL, otherwise sets it.
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $url
             *
             * @return SFTP
             * @throws SE When the URL is not a string
             */
            function url($url = '') {
                if ($url === '') {
                    return $this->url;
                } elseif (is_string($url)) {
                    $this->url = $url;
                    \Log::debug('SFTP URL set to ' . $url);
                } else {
                    throw new SE('Invalid URL', SE::E_URL_INVALID);
                }

                return $this;
            }

            /**
             * If no parameter is passed gets the SFTP username, otherwise sets it.
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $user The username
             *
             * @throws SE When $user isn't scalar
             * @return SFTP
             */
            function user($user = '') {
                if ($user === '') {
                    return $this->user;
                } elseif (is_scalar($user)) {
                    $this->user = $user;
                    \Log::debug('SFTP user set to ' . $user);
                } else {
                    throw new SE('Invalid username', SE::E_USER_INVALID);
                }

                return $this;
            }

            /**
             * Sets the SFTP public key path
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $pubkey The path
             *
             * @throws SE When $pubkey isn't a string
             * @return SFTP
             */
            function pubkey($pubkey = '') {
                if ($pubkey === '') {
                    return $this->pubkey;
                } elseif (is_string($pubkey)) {
                    $this->pubkey = $pubkey;
                    \Log::debug('SFTP pubkey set');
                } else {
                    throw new SE('$pubkey must be a valid path', SE::E_PATH_INVALID);
                }

                return $this;
            }

            /**
             * If no parameter is passed gets the SFTP private key path, otherwise sets it
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $privkey The path
             *
             * @throws SE When the $privkey isn't a string
             * @return SFTP
             */
            function privkey($privkey = '') {
                if ($privkey === '') {
                    return $this->privkey;
                } elseif (is_string($privkey)) {
                    $this->privkey = $privkey;
                    \Log::debug('SFTP privkey set');
                } else {
                    throw new SE('$privkey must be a valid path', SE::E_PATH_INVALID);
                }

                return $this;
            }

            /**
             * If no parameter is passed gets the SFTP private key password, otherwise sets it
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $pw The password
             *
             * @throws SE When the password isn't scalar
             * @return SFTP
             */
            function pw($pw = '') {
                if ($pw === '') {
                    return $this->pw;
                } elseif (is_scalar($pw)) {
                    $this->pw = $pw;
                    \Log::debug('SFTP password set');
                } else {
                    throw new SE('Invalid password provided', SE::E_PW_INVALID);
                }

                return $this;
            }

            /**
             * If no argument is passed, gets the working directory, otherwise sets it.
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param mixed $dir
             *
             * @throws SE When the name is invalid
             * @return SFTP
             */
            function dir($dir = -1) {
                if ($dir === -1) {
                    return $this->dir;
                } elseif (is_scalar($dir)) {
                    $dir = trim($dir, DIRECTORY_SEPARATOR . ' ');
                    if (!$dir || $dir == DIRECTORY_SEPARATOR) {
                        $dir = '.' . DIRECTORY_SEPARATOR;
                    }
                    $this->dir = $dir;
                    \Log::debug('Directory set to ' . $dir);
                } else {
                    throw new SE('Directory name not scalar', SE::E_NAME_INVALID);
                }

                return $this;
            }

        }
    }
