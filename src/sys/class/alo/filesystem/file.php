<?php

   namespace Alo\FileSystem;

   use Alo\Exception\FileSystemException as FE;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Object-oriented file handler
       *
       * @author Arturas Molcanovas <a.molcanovas@gmail.com>
       */
      class File extends AbstractFileSystem {

         /**
          * Open for reading only; place the file pointer at the beginning of the file.
          *
          * @var string
          */
         const M_READ_EXISTING_BEGIN = 'r';

         /**
          * Open for reading and writing; place the file pointer at the beginning of the file.
          *
          * @var string
          */
         const M_RW_EXISTING_BEGIN = 'r+';

         /**
          * Open for writing only; place the file pointer at the beginning of the file
          * and truncate the file to zero length. If the file does not exist, attempt
          * to create it.
          *
          * @var string
          */
         const M_WRITE_TRUNCATE_BEGIN = 'w';

         /**
          * Open for reading and writing; place the file pointer at the beginning of
          * the file and truncate the file to zero length. If the file does not exist,
          * attempt to create it.
          *
          * @var string
          */
         const M_RW_TRUNCATE_BEGIN = 'w+';

         /**
          * Open for writing only; place the file pointer at the end of the file. If
          * the file does not exist, attempt to create it.
          *
          * @var string
          */
         const M_WRITE_END = 'a';

         /**
          * Open for reading and writing; place the file pointer at the end of the file.
          * If the file does not exist, attempt to create it.
          *
          * @var string
          */
         const M_RW_END = 'a+';

         /**
          * Create and open for writing only; place the file pointer at the beginning
          * of the file. If the file already exists, the fopen() call will fail by
          * returning FALSE and generating an error of level E_WARNING. If the file
          * does not exist, attempt to create it. This is equivalent to specifying
          * O_EXCL|O_CREAT flags for the underlying open(2) system call.
          *
          * @var string
          */
         const M_WRITE_NONEXIST_BEGIN = 'x';

         /**
          * Create and open for reading and writing; otherwise it has the same behavior as
          * M_WRITE_NONEXIST_BEGIN
          *
          * @var string
          * @see self::M_WRITE_NONEXIST_BEGIN
          */
         const M_RW_NONEXIST_BEGIN = 'x+';

         /**
          * Open the file for writing only. If the file does not exist, it is created.
          * If it exists, it is neither truncated (as opposed to M_WRITE_TRUNCATE_BEGIN), nor the call to
          * this function fails (as is the case with M_WRITE_NONEXIST_BEGIN). The file pointer is positioned
          * on the beginning of the file. This may be useful if it's desired to get an
          * advisory lock (see flock()) before attempting to modify the file, as using
          * M_WRITE_NONEXIST_BEGIN could truncate the file before the lock was
          * obtained (if truncation is desired, ftruncate() can be used after the lock
          * is requested).
          *
          * @var string
          * @see self::M_WRITE_NONEXIST_BEGIN
          * @see self::M_WRITE_TRUNCATE_BEGIN
          */
         const M_WRITE_BEGIN = 'c';

         /**
          * Open the file for reading and writing; otherwise it has the same behavior as
          * M_WRITE_BEGIN.
          *
          * @var string
          * @see self::M_WRITE_BEGIN;
          */
         const M_RW_BEGIN = 'c+';

         /**
          * Whether GZIP is installed
          *
          * @var boolean
          */
         protected static $gz;

         /**
          * The file content
          *
          * @var string
          */
         protected $content;

         /**
          * The file name
          *
          * @var string
          */
         protected $name;

         /**
          * The file directory
          *
          * @var string
          */
         protected $dir;

         /**
          * The full file path. Updates with every setName & setDir
          *
          * @var string
          * @see self::setName()
          * @see self::setDir()
          */
         protected $filepath;

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          */
         function __construct() {
            parent::__construct();

            $this->dir = DIR_TMP;
            self::$gz  = function_exists('gzencode');
         }

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @return File
          */
         static function file() {
            return new File();
         }

         /**
          * Converts a filesize for display
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param int $size The file size in bytes
          *
          * @return string The file size in its largest form, e.g. 1024 bytes become 1KB;
          */
         static function convertSize($size) {
            if(is_numeric($size)) {
               $size = (int)$size;

               if($size < 1024) {
                  return $size . 'B';
               } elseif($size < 1048576) {
                  return round($size / 1024, 2) . 'KB';
               } elseif($size < 1099511627776) {
                  return round($size / 1048576, 2) . 'MB';
               } elseif($size < 1125899906842624) {
                  return round($size / 1099511627776, 2) . 'GB';
               } else {
                  return round($size / 1125899906842624, 2) . 'TB';
               }
            }

            return $size;
         }

         /**
          * Appends the file contents on the disc
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws FE When fopen fails
          * @return boolean
          */
         function append() {
            \Log::debug('Appended the file');

            return $this->doWrite(self::M_WRITE_END);
         }

         /**
          * Performs a write operation
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $mode The write mode - see class constants
          *
          * @return File
          * @throws FE When fopen fails
          */
         protected function doWrite($mode) {
            $this->checkParams();
            if(!$fp = @fopen($this->filepath, $mode)) {
               throw new FE('Failed to fopen file ' . $this->filepath, FE::E_FOPEN_FAIL);
            } else {
               flock($fp, LOCK_EX);
               fwrite($fp, $this->content);
               flock($fp, LOCK_UN);
               fclose($fp);
               \Log::debug('Wrote ' . $this->filepath . ' contents');

               return $this;
            }
         }

         /**
          * Checks if the dir and name are set
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return File
          * @throws FE When the file path is not set
          */
         protected function checkParams() {
            if(!$this->dir || !$this->name) {
               throw new FE('File path not set', FE::E_PATH_NOT_SET);
            }

            return $this;
         }

         /**
          * Gets the file extension based on the currently set filename
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param int     $depth            The depth to search for, e.g. if the file name is
          *                                  foo.tar.gz, depth=1 would return "gz" while depth=2 would return .tar.gz
          * @param boolean $only_that_member Only effective if $depth > 1. If FALSE
          *                                  and the extension is tar.gz, will return "tar.gz", if TRUE, will return "tar".
          *
          * @return string
          * @uses   self::get_extension()
          */
         function getExtension($depth = 1, $only_that_member = false) {
            return $this->name ? self::get_extension($this->name, $depth, $only_that_member) : null;
         }

         /**
          * Gets the file extension based on name
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string  $filename         The file name
          * @param int     $depth            The depth to search for, e.g. if the file name is
          *                                  foo.tar.gz, depth=1 would return "gz" while depth=2 would return .tar.gz
          * @param boolean $only_that_member Only effective if $depth > 1. If FALSE
          *                                  and the extension is tar.gz, will return "tar.gz", if TRUE, will return "tar".
          *
          * @return string
          */
         static function get_extension($filename, $depth = 1, $only_that_member = false) {
            $exploded = explode('.', strtolower($filename));
            if(!is_numeric($depth) || $depth < 1) {
               $depth = 1;
            }

            return $only_that_member && $depth > 1 ? get($exploded[count($exploded) - $depth]) :
               implode('.', array_slice($exploded, $depth * -1));
         }

         /**
          * Alias for self::unlink()
          *
          * @author Art <a.molcanovas@gmail.com>
          * @uses   self::unlink()
          * @return boolean
          */
         function delete() {
            return $this->unlink();
         }

         /**
          * Deletes the file
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws FE When the file path is not set
          * @return boolean
          */
         function unlink() {
            $this->checkParams();
            if(unlink($this->filepath)) {
               \Log::debug('Deleted ' . $this->filepath);

               return true;
            } else {
               \Log::error('Failed to delete ' . $this->filepath);

               return false;
            }
         }

         /**
          * Gzip-encodes the fetched content
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param int $level Compression strength (0-9)
          *
          * @throws FE When the file doesn't exist
          * @return boolean
          */
         function gzipContent($level = 9) {
            if(self::$gz) {
               if(!$this->content) {
                  if($this->filepath && $this->fileExists()) {
                     \Log::debug('File contents not present for gzip: reading them');
                     $this->read();
                  } else {
                     \Log::error('Failed to gzip file contents: file not found');
                  }
               }

               if($this->content) {
                  $this->content = gzencode($this->content, $level);
                  \Log::debug('Gzipped file contents');

                  return true;
               } else {
                  \Log::error('Failed to gzip file contents: content not present');
               }
            } else {
               \Log::error('Failed to gzip file contents: extension not loaded');
            }

            return false;
         }

         /**
          * Checks whether the file exists at the set path
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws FE When the file path is not set
          * @return boolean
          */
         function fileExists() {
            $this->checkParams();

            return file_exists($this->filepath);
         }

         /**
          * Reads the file contents into $this->content
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return boolean
          * @throws FE When the file doesn't exist
          */
         function read() {
            $this->checkParams();
            if(file_exists($this->filepath)) {
               $this->content = file_get_contents($this->filepath, true);
               \Log::debug('Read ' . $this->filepath . ' contents');

               return true;
            } else {
               throw new FE($this->filepath . ' doesn\'t exist', FE::E_FILE_NOT_EXISTS);
            }
         }

         /**
          * Gzip-decodes the fetched content
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws FE When the file doesn't exist
          * @return boolean
          */
         function ungzipContent() {
            if(self::$gz) {
               if(!$this->content) {
                  if($this->filepath && $this->fileExists()) {
                     \Log::debug('File contents not present for ungzip: reading them');
                     $this->read();
                  } else {
                     \Log::error('Failed to ungzip file contents: file not found');
                  }
               }

               if($this->content) {
                  $this->content = gzdecode($this->content);
                  \Log::debug('Ungzipped file contents');

                  return true;
               } else {
                  \Log::error('Failed to ungzip file contents: content not present');
               }
            } else {
               \Log::error('Failed to ungzip file contents: extension not loaded');
            }

            return false;
         }

         /**
          * Overwrites the file contents on the disc
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws FE When the file path is not set
          * @return File
          */
         function write() {
            \Log::debug('Overwriting file contents');

            return $this->doWrite(self::M_WRITE_TRUNCATE_BEGIN);
         }

         /**
          * Returns a string representation of the object data
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function __toString() {
            return \lite_debug($this);
         }

         /**
          * Returns the file's path in the system
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getFilePath() {
            return $this->filepath;
         }

         /**
          * If no argument is passed, gets the file name, otherwise sets it
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $name The name
          *
          * @return File|string
          * @throws FE When the name is invalid
          */
         function name($name = '') {
            if($name === '') {
               return $this->name;
            } elseif(is_scalar($name)) {
               $this->replace($name);
               $this->name = trim($name, DIRECTORY_SEPARATOR);
               $this->updatePath();
               \Log::debug('File name set to ' . $this->name);

            } else {
               throw new FE('File name invalid', FE::E_NAME_INVALID);
            }

            return $this;
         }

         /**
          * Updates the file path when the directory or file name are changed
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return File
          */
         protected function updatePath() {
            $this->filepath = $this->dir . DIRECTORY_SEPARATOR . $this->name;

            return $this;
         }

         /**
          * If no argument is passed, gets the directory name, otherwise sets it
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $dir The directory
          *
          * @return File|string
          * @throws FE When the name is invalid
          */
         function dir($dir = '') {
            if($dir === '') {
               return $this->dir;
            } elseif(is_scalar($dir)) {
               $this->replace($dir);
               $this->dir = rtrim($dir, DIRECTORY_SEPARATOR);
               $this->updatePath();
               \Log::debug('Directory name set to ' . $dir);

            } else {
               throw new FE('Directory name invalid', FE::E_NAME_INVALID);
            }

            return $this;
         }

         /**
          * Scans the directory for files
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return array
          */
         function scandir() {
            return $this->dir ? scandir($this->dir) : [];
         }

         /**
          * If no argument is passed, gets the currently set content, otherwise sets it
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $content Content to set
          *
          * @throws FE When content is not scalar
          * @return File|string
          */
         function content($content = '~none~') {
            if($content === '~none~') {
               return $this->content;
            } elseif(is_scalar($content)) {
               \Log::debug('Overwrote file contents');
               $this->content = $content;

            } else {
               throw new FE('Content is not scalar!', FE::E_CONTENT_INVALID);
            }

            return $this;
         }

         /**
          * Clears the file content
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return File
          */
         function clearContent() {
            \Log::debug('Cleared file contents');
            $this->content = null;

            return $this;
         }

         /**
          * Appends the file content
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $c The content
          *
          * @return File
          */
         function addContent($c) {
            \Log::debug('Appended file contents');
            $this->content .= $c;

            return $this;
         }

      }
   }
