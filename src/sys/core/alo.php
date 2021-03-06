<?php

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * The global framework class
         *
         * @author Art <a.molcanovas@gmail.com>
         */
        class Alo {

            /**
             * SFTP connection manager
             *
             * @var Alo\SFTP
             */
            static $sftp;

            /**
             * Code profiler
             *
             * @var Alo\Profiler
             */
            static $profiler;

            /**
             * File manager
             *
             * @var Alo\FileSystem\File
             */
            static $file;

            /**
             * Email manager
             *
             * @var Alo\Email
             */
            static $email;

            /**
             * Object-oriented Curl wrapper
             *
             * @var Alo\Curl
             */
            static $curl;

            /**
             * Crontab manager
             *
             * @var Alo\Cron
             */
            static $cron;

            /**
             * HTML form validator
             *
             * @var Alo\Validators\Form
             */
            static $formValidator;

            /**
             * Database connection
             *
             * @var Alo\Db\AbstractDb
             */
            static $db;

            /**
             * Cache instance
             *
             * @var Alo\Cache\AbstractCache
             */
            static $cache;

            /**
             * The loaded controller
             *
             * @var Alo\Controller\AbstractController
             */
            static $controller;

            /**
             * The routing class
             *
             * @var Alo\Controller\Router
             */
            static $router;

            /**
             * Windows service handler
             *
             * @var Alo\Windows\Service
             */
            static $service;

            /**
             * Download manager
             *
             * @var Alo\CLI\Downloader
             */
            static $downloader;

            /**
             * Locale manager
             *
             * @var Alo\Locale
             */
            static $locale;

            /**
             * Loads a configuration file based on environment: from DIR_SYS/config during setup & DIR_APP/config during
             * production/development
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $path        The config file relative path without the file extension, e.g. to load a file found
             *                            in config/db/mysql.php provide db/mysql
             * @param bool   $returnPath  If set to true it will return the calculated path instead of requiring the file
             *
             * @return string|bool The path is $returnPath is true, TRUE if it is false
             */
            static function loadConfig($path, $returnPath = false) {
                $dir  = defined('ENVIRONMENT') && ENVIRONMENT === ENV_SETUP ? DIR_SYS . 'config' . DIRECTORY_SEPARATOR :
                    DIR_CONFIG;
                $path = strtolower($path);
                if (substr($path, -4) == '.php') {
                    $path = substr($path, 0, -4);
                }

                /** @noinspection PhpUnusedLocalVariableInspection */
                $finalPath = '';
                if (file_exists($dir . $path . '.php')) {
                    $finalPath = $dir . $path . '.php';
                } else {
                    trigger_error('Configuration file ' . $path .
                                  ' not found in the application folder. Attempting to load from sys.', E_USER_WARNING);
                    $finalPath = DIR_SYS . 'config' . DIRECTORY_SEPARATOR . $path . '.php';
                }

                if ($returnPath) {
                    return $finalPath;
                } else {
                    include_once $finalPath;

                    return true;
                }
            }

            /**
             * Performs include() only if a file exists
             *
             * @param string $path Path to the file
             *
             * @return bool true if the file exists, false if not.
             */
            static function includeifexists($path) {
                if (file_exists($path)) {
                    include $path;

                    return true;
                }

                return false;
            }

            /**
             * Performs include_once() only if a file exists
             *
             * @param string $path Path to the file
             *
             * @return bool true if the file exists, false if not.
             */
            static function includeonceifexists($path) {
                if (file_exists($path)) {
                    include_once $path;

                    return true;
                }

                return false;
            }

            /**
             * Check if the server is running Windows
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return bool
             */
            static function serverIsWindows() {
                return substr(strtoupper(php_uname('s')), 0, 3) === 'WIN';
            }
        }

        \Log::debug('Alo framework class initialised');
    }
