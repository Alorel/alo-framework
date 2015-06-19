<?php

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      ob_start();
      require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'log.php';
      include_once DIR_SYS . 'external' . DIRECTORY_SEPARATOR . 'kint' . DIRECTORY_SEPARATOR . 'Kint.class.php';
      require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'handler.php';

      spl_autoload_register('\Alo\Handler::autoloader');
      if(!defined('PHPUNIT_RUNNING')) {
         set_error_handler('\Alo\Handler::error', ini_get('error_reporting'));
         set_exception_handler('\Alo\Handler::ecxeption');
      }

      /**
       * A shortcut to isset($var) ? $var : null
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param mixed $var The variable to "return"
       *
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
         if(!Kint::enabled()) {
            return null;
         } else {
            ob_start();
            $args = func_get_args();
            call_user_func_array(['Kint', 'dump'], $args);

            return ob_get_clean();
         }
      }

      /**
       * Check if the server is running Windows
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return bool
       */
      function server_is_windows() {
         return substr(strtoupper(php_uname('s')), 0, 3) === 'WIN';
      }

      /**
       * Returns a lite debug string of passed on variables
       *
       * @return string
       */
      function lite_debug() {
         if(!Kint::enabled()) {
            return '';
         } else {
            ob_start();
            $argv = func_get_args();
            echo '<pre>';
            foreach($argv as $k => $v) {
               $k && print("\n\n");
               echo s($v);
            }
            echo '</pre>' . "\n";

            return ob_get_clean();
         }
      }

      /**
       * Returns a very precise timestamp
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param float $microtime Optionally, supply your own microtime
       *
       * @return string Y-m-d H:i:s:{milliseconds}
       */
      function timestamp_precise($microtime = null) {
         if(!$microtime) {
            $microtime = microtime(true);
         }
         $t = explode('.', $microtime);

         return date('Y-m-d H:i:s', $t[0]) . ':' . round($t[1] / 10);
      }

      if(!function_exists('getallheaders')) {

         /**
          * Implement getallheaders() for non-apache servers
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return array
          */
         function getallheaders() {
            $headers = [];
            foreach($_SERVER as $name => $value) {
               if(substr($name, 0, 5) == 'HTTP_') {
                  $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] =
                     $value;
               }
            }

            return $headers;
         }
      }

      /**
       * Escapes sensitive characters for HTML5 output
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $str The input string
       *
       * @return string
       */
      function escape_html5($str) {
         return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5);
      }

      /**
       * Performs include() only if a file exists
       *
       * @param string $path Path to the file
       *
       * @return bool true if the file exists, false if not.
       */
      function includeifexists($path) {
         if(file_exists($path)) {
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
      function includeonceifexists($path) {
         if(file_exists($path)) {
            include_once $path;

            return true;
         }

         return false;
      }

      /**
       * Triggers a PHP-level error with the level E_USER_ERROR
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $msg Error message
       *
       * @link   http://php.net/manual/en/function.trigger-error.php
       * @return bool
       */
      function php_error($msg) {
         return trigger_error($msg, E_USER_ERROR);
      }

      /**
       * Triggers a PHP-level error with the level E_USER_WARNING
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $msg Error message
       *
       * @link   http://php.net/manual/en/function.trigger-error.php
       * @return bool
       */
      function php_warning($msg) {
         return trigger_error($msg, E_USER_WARNING);
      }

      /**
       * Triggers a PHP-level error with the level E_USER_NOTICE
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $msg Error message
       *
       * @link   http://php.net/manual/en/function.trigger-error.php
       * @return bool
       */
      function php_notice($msg) {
         return trigger_error($msg, E_USER_NOTICE);
      }

      /**
       * Triggers a PHP-level error with the level E_USER_DEPRECATED
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $msg Error message
       *
       * @link   http://php.net/manual/en/function.trigger-error.php
       * @return bool
       */
      function php_deprecated($msg) {
         return trigger_error($msg, E_USER_DEPRECATED);
      }

      require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'alo.php';

      /**
       * Initialises a session
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param Alo\Db\MySQL|Alo\Cache\AbstractCache $dependcyObject Session handlers have a dependency, e.g. a MySQL
       *                                                             instance for MySQLSession, a RedisWrapper instance
       *                                                             for RedisSession etc. You can provide an object
       *                                                             reference containing such an instance here,
       *                                                             otherwise Alo::$db/Alo::$cache will be used.
       */
      function initSession(&$dependcyObject = null) {
         if(session_status() !== PHP_SESSION_ACTIVE) {
            Alo::loadConfig('session');
            session_set_cookie_params(ALO_SESSION_TIMEOUT, null, null, ALO_SESSION_SECURE, true);
            session_name(ALO_SESSION_COOKIE);

            $handler = ALO_SESSION_HANDLER;
            /** @var Alo\Session\AbstractSession $handler */
            $handler = new $handler($dependcyObject);

            session_set_save_handler($handler, true);
            session_start();
            $handler->identityCheck();
         } else {
            php_warning('A session has already been started');
         }
      }

      includeonceifexists(DIR_APP . 'core' . DIRECTORY_SEPARATOR . 'autoload.php');

      if(!defined('PHPUNIT_RUNNING')) {
         Alo::$router = new Alo\Controller\Router();
         Alo::$router->init();
      }
   }
