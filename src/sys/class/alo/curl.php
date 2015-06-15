<?php

   namespace Alo;

   use Alo\Exception\ExtensionException as EE;
   use Log;

   if(!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * Object-oriented cURL wrapper
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   class cURL {

      /**
       * Static reference to the last instance of the class
       *
       * @var cURL
       */
      static $this;

      /**
       * The cURL resource
       *
       * @var resource
       */
      protected $ch;

      /**
       * Result of exec()
       *
       * @var mixed
       * @see self::exec()
       */
      protected $exec;

      /**
       * Error number of exec()
       *
       * @var int
       * @see self::exec()
       */
      protected $errno;

      /**
       * Error message of exec()
       *
       * @var string
       * @see self::exec()
       */
      protected $error;

      /**
       * Whether the connection is open
       *
       * @var boolean
       */
      protected $is_open;

      /**
       * Instantiates the library
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $url Optionally, the URL for curl_init()
       *
       * @throws EE When the curl extention is not loaded
       */
      function __construct($url = null) {
         if(!function_exists('curl_init')) {
            throw new EE('cURL PHP extension not loaded', EE::E_EXT_NOT_LOADED);
         } else {
            $this->ch      = curl_init();
            $this->is_open = true;
            curl_setopt_array($this->ch,
                              [
                                 CURLOPT_RETURNTRANSFER => true,
                                 CURLOPT_SSLVERSION     => 3,
                                 CURLOPT_SSL_VERIFYPEER => true,
                                 CURLOPT_FOLLOWLOCATION => true
                              ]);

            Log::debug('Initialised cURL');

            if($url) {
               $this->setURL($url);
            }
         }

         self::$this = &$this;
      }

      /**
       * Sets the connection URL
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $url The URL
       *
       * @return cURL
       */
      function setURL($url) {
         Log::debug('Set cURL URL to ' . $url);
         curl_setopt($this->ch, CURLOPT_URL, $url);

         return $this;
      }

      /**
       * Instantiates the library
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $url Optionally, the URL for curl_init()
       *
       * @throws EE When the curl extention is not loaded
       *
       * @return cURL
       */
      static function cURL($url = null) {
         return new cURL($url);
      }

      /**
       * A static wrapper function for __construct()
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $url Optionally, the URL for curl_init()
       *
       * @return cURL
       */
      static function init($url = null) {
         return new cURL($url);
      }

      /**
       * Sets CURLOPT_NOPROGRESS to FALSE and supplies the progress function
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param callable $callable The function
       *
       * @return bool
       */
      function setProgressFunction($callable) {
         if(!is_callable($callable)) {
            return false;
         } else {
            curl_setopt_array($this->ch,
                              [
                                 CURLOPT_NOPROGRESS       => false,
                                 CURLOPT_PROGRESSFUNCTION => $callable
                              ]);

            return true;
         }
      }

      /**
       * Set whether cURL should time out
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param boolean $enabled The switch
       *
       * @return cURL
       */
      function notimeout($enabled = true) {
         $a = $enabled ? [
            CURLOPT_CONNECTTIMEOUT => 86400,
            CURLOPT_TIMEOUT        => 86400
         ] : [
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TIMEOUT        => 1
         ];

         Log::debug('cURL timeout ' . ($enabled ? 'en' : 'dis') . 'abled');

         return $this->setopt_array($a);
      }

      /**
       * Sets an array of options
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param array $a An array specifying which options to set and their values. The keys should be valid
       *                 curl_setopt() constants or their integer equivalents.
       *
       * @return cURL
       * @link   http://php.net/manual/en/function.curl-setopt-array.php
       */
      function setopt_array(array $a) {
         curl_setopt_array($this->ch, $a);

         return $this;
      }

      /**
       * Toggles lax SSL verification mode which doesn't check certificates
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param boolean $enabled Whether the mode is enabled or disabled
       *
       * @return cURL
       */
      function laxSSLMode($enabled = true) {
         Log::debug(($enabled ? 'Dis' : 'En') . 'abled CURLOPT_SSL_VERIFYPEER');
         curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, !$enabled);

         return $this;
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
       * URL encodes the given string
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $str The string
       *
       * @return string The escaped string
       * @link   http://php.net/manual/en/function.curl-escape.php
       */
      function escape($str) {
         return curl_escape($this->ch, $str);
      }

      /**
       * Pause and unpause a connection
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param int $bitmask One of CURLPAUSE_* constants.
       *
       * @return int An error code (CURLE_OK for no error).
       * @link   http://php.net/manual/en/function.curl-pause.php
       */
      function pause($bitmask) {
         Log::debug('Changed the cURL pause state');

         return curl_pause($this->ch, $bitmask);
      }

      /**
       * Gets cURL version information
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param int $age
       *
       * @return array
       * @link   http://php.net/manual/en/function.curl-version.php
       */
      function version($age = CURLVERSION_NOW) {
         return curl_version($age);
      }

      /**
       * Checks whether the last transfer was successful
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return boolean|int If successful - true, if not & cURL error code exists
       *         - cURL error code, false otherwise
       */
      function wasSuccessful() {
         if($this->errno !== CURLE_OK) {
            return 'cURL error #' . $this->errno;
         } else {
            $code = $this->getinfo(CURLINFO_HTTP_CODE);
            if(!in_array(substr('' . $this->getinfo(CURLINFO_HTTP_CODE), 0, 1), [1, 2, 3])) {
               return 'HTML response ' . $code;
            } else {
               return true;
            }
         }
      }

      /**
       * Get information regarding a specific transfer
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param int $opt One of the cURL constants
       *
       * @return mixed
       * @link   http://php.net/manual/en/function.curl-getinfo.php
       */
      function getinfo($opt = 0) {
         return curl_getinfo($this->ch, $opt);
      }

      /**
       * Reset all options of a libcurl session handle
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return cURL
       * @link   http://php.net/manual/en/function.curl-reset.php
       */
      function reset() {
         Log::debug('Reset cURL');
         curl_reset($this->ch);
         curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

         return $this;
      }

      /**
       * Decodes the given URL encoded string
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $str
       *
       * @return string The decoded string
       * @link   http://php.net/manual/en/function.curl-unescape.php
       */
      function unescape($str) {
         return curl_unescape($this->ch, $str);
      }

      /**
       * Auto-cleanup
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      function __destruct() {
         $this->close();
      }

      /**
       * Closes a cURL connection
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return cURL
       * @link   http://php.net/manual/en/function.curl-close.php
       */
      function close() {
         if($this->is_open) {
            @curl_close($this->ch);
            $this->is_open = false;
            Log::debug('Closed cURL connection');
         }

         return $this;
      }

      /**
       * Gets the results of a cURL exec. If $url is set, will exec on that URL
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $url Optional URL override
       *
       * @return mixed The results of exec()
       */
      function get($url = null) {
         if($url !== null) {
            $this->setURL($url)->exec();
         }

         return $this->exec;
      }

      /**
       * Executes the cURL connection parameters
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return cURL
       * @link   http://php.net/manual/en/function.curl-exec.php
       */
      function exec() {
         Log::debug('Started cURL exec');
         $this->exec  = curl_exec($this->ch);
         $this->errno = curl_errno($this->ch);
         $this->error = curl_error($this->ch);
         Log::debug('Finished curl exec');

         return $this;
      }

      /**
       * Returns the error message of the last exec()
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       * @see    self::exec()
       * @link   http://php.net/manual/en/function.curl-error.php
       */
      function error() {
         return $this->error;
      }

      /**
       * Returns the error number of the last exec() or 0 if no error occurred
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return int
       * @see    self::exec()
       * @link   http://php.net/manual/en/function.curl-errno.php
       */
      function errno() {
         return $this->errno;
      }

      /**
       * Wrapper for setopt()
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $name  Param name
       * @param mixed  $value Param value
       */
      function __set($name, $value) {
         $this->setopt($name, $value);
      }

      /**
       * Sets an option
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param int   $name  The option - see cURL constants
       * @param mixed $value The option value
       *
       * @return cURL
       * @link   http://php.net/manual/en/function.curl-setopt.php
       */
      function setopt($name, $value) {
         if($name === CURLOPT_POSTFIELDS) {
            curl_setopt($this->ch, CURLOPT_POST, true);
         }

         curl_setopt($this->ch, $name, $value);

         return $this;
      }

   }