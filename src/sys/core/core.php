<?php

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   ob_start();
   require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'log.php';
   include_once DIR_SYS . 'external' . DIRECTORY_SEPARATOR . 'kint' . DIRECTORY_SEPARATOR . '_main.php';
   require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'handler.php';

   spl_autoload_register('\Alo\Handler::autoloader');
   set_error_handler('\Alo\Handler::error', ini_get('error_reporting'));
   set_exception_handler('\Alo\Handler::ecxeption');

   /**
    * A shortcut to isset($var) ? $var : null
    *
    * @author Art <a.molcanovas@gmail.com>
    * @param mixed $var The variable to "return"
    * @return mixed
    */
   function get(&$var) {
      return isset($var) ? $var : null;
   }

   /**
    * Returns a debug string of the passed on variables
    *
    * @return string
    */
   function debug() {
      if (!Kint::enabled()) {
         return null;
      } else {
         ob_start();
         $args = func_get_args();
         call_user_func_array(['Kint', 'dump'], $args);

         return ob_get_clean();
      }
   }

   /**
    * Returns a lite debug string of passed on variables
    *
    * @return string
    */
   function lite_debug() {
      if (!Kint::enabled()) {
         return '';
      } else {
         ob_start();
         $argv = func_get_args();
         echo '<pre>';
         foreach ($argv as $k => $v) {
            $k && print("\n\n");
            echo kintLite($v);
         }
         echo '</pre>' . "\n";

         return ob_get_clean();
      }
   }

   /**
    * Returns a very precise timestamp
    *
    * @author Art <a.molcanovas@gmail.com>
    * @param float $microtime Optionally, supply your own microtime
    * @return string Y-m-d H:i:s:{milliseconds}
    */
   function timestamp_precise($microtime = null) {
      if (!$microtime) {
         $microtime = microtime(true);
      }
      $t = explode('.', $microtime);

      return date('Y-m-d H:i:s', $t[0]) . ':' . round($t[1] / 10);
   }

   /**
    * Escapes a string or array
    *
    * @author Art <a.molcanovas@gmail.com>
    * @param String|array $item The item to be escaped
    * @return String|array
    */
   function escape($item) {
      if (is_array($item)) {
         foreach ($item as &$v) {
            $v = (is_string($v) || is_numeric($v)) ? htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8', false) : null;
         }

         return $item;
      } else {
         return (is_string($item) || is_numeric($item)) ? htmlspecialchars($item, ENT_QUOTES | ENT_HTML5, 'UTF-8', false) : null;
      }
   }

   /**
    * Returns an unhashed browser/IP fingerprint
    *
    * @author Art <a.molcanovas@gmail.com>
    * @return string
    */
   function getFingerprint() {
      return '$%c0hYlc$kn!rZF' . get($_SERVER['HTTP_USER_AGENT'])
      . get($_SERVER['HTTP_DNT']) . '^#J!kCRh&H4CKav'
      . get($_SERVER['HTTP_ACCEPT_LANGUAGE']) . 'h0&ThYYxk4YOD!g' . get($_SERVER['REMOTE_ADDR']);
   }

   /**
    * Generates a unique identifier
    *
    * @author Art <a.molcanovas@gmail.com>
    * @param string     $hash    Hash algorithm
    * @param string|int $prefix  Prefix for the identifier
    * @param int        $entropy Number of pseudo bytes used in entropy
    * @return string
    */
   function getUniqid($hash = 'md5', $prefix = null, $entropy = 5) {
      $str = uniqid(mt_rand(1, 100000000) . getFingerprint(), true)
         . $prefix;

      if (function_exists('openssl_random_pseudo_bytes')) {
         $str .= openssl_random_pseudo_bytes($entropy);
      }

      return hash($hash, $str);
   }

   if (!function_exists('getallheaders')) {

      /**
       * Implement getallheaders() for non-apache servers
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return array
       */
      function getallheaders() {
         $headers = [];
         foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
         }

         return $headers;
      }
   }

   require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'alo.php';

   if (!defined('PHPUNIT_RUNNING') || !PHPUNIT_RUNNING) {
      Alo::$router = new Alo\Controller\Router();
      Alo::$router->init();
   }