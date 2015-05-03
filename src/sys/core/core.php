<?php

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   ob_start();
   require_once DIR_SYS . 'core/log.php';
   include_once DIR_SYS . 'external/kint/_main.php';

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

   /**
    * Used to automatically load class files
    *
    * @author Art <a.molcanovas@gmail.com>
    * @param string $name Class name
    */
   function alo_autoloader($name) {
      $name = ltrim(strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $name)), '/') . '.php';
      $locations = [
         DIR_APP . 'class',
         DIR_SYS . 'class',
         DIR_APP . 'interface',
         DIR_APP . 'traits'
      ];

      foreach ($locations as $l) {
         if (file_exists($l . DIRECTORY_SEPARATOR . $name)) {
            include_once $l . DIRECTORY_SEPARATOR . $name;
            break;
         }
      }
   }

   /**
    * The dev error handler
    *
    * @author Art <a.molcanovas@gmail.com>
    * @param int    $errno   The level of the error raised
    * @param string $errstr  The error message
    * @param string $errfile The filename that the error was raised in
    * @param int    $errline The line number the error was raised at
    */
   function alo_error_handler($errno, $errstr, $errfile, $errline) {
      $type = $errno;

      switch ($errno) {
         case E_NOTICE:
         case E_USER_NOTICE:
            $type = 'NOTICE';
            break;
         case E_ERROR:
         case E_USER_ERROR:
         case E_COMPILE_ERROR:
         case E_RECOVERABLE_ERROR:
         case E_CORE_ERROR:
            $type = 'ERROR';
            break;
         case E_WARNING:
         case E_USER_WARNING:
         case E_CORE_WARNING:
            $type = 'WARNING';
            break;
      }

      $f = explode(DIR_INDEX, $errfile)[1];

      echo '<div style="text-align:center;margin:12px auto 12px auto">'
         . '<div style="text-align:left;display:inline-block;padding:2px;background:#FD8C7F;border:2px solid #F00;color:#000">'
         . '<div style="font-weight:bold;margin-bottom:1em">'
         . $type . ' : ' . $errstr
         . '</div>'
         . '<div>Raised in <span style="font-weight:bold">' . $f . ': ' . $errline . '</span></div>'
         . '<div>Backtrace:</div>'
         . '<table cellpadding="2" border="1" style="border-collapse:collapse;width:100%;text-align:center">'
         . '<thead>'
         . '<tr>'
         . '<th>#</th>'
         . '<th>Function</th>'
         . '<th>Args</th>'
         . '<th>Location</th>'
         . '<th>Line</th>'
         . '</tr>'
         . '</thead>'
         . '<tbody>';

      $trace = array_reverse(debug_backtrace());
      array_pop($trace);

      foreach ($trace as $k => $v) {
         $func = $loc = $line = '';

         if (isset($v['class'])) {
            $func = $v['class'];
         }
         if (isset($v['type'])) {
            $func .= $v['type'];
         }
         if (isset($v['function'])) {
            $func .= $v['function'] . '()';
         }
         if (!$func) {
            $func = 'unknown';
         }

         if (isset($v['file'])) {
            $loc = explode(DIR_INDEX, $v['file'])[1];
         }
         if (isset($v['line'])) {
            $line .= $v['line'];
         }

         echo '<tr>'
            . '<td>' . $k . '</td>'
            . '<td>' . $func . '</td>'
            . '<td style="text-align:left">' . ($v['args'] ? '<pre>' . preg_replace("/\n(\s*)(\t*)\(/i", "$1$2(", print_r($v['args'], true)) . '</pre>' : '') . '</td>'
            . '<td>' . $loc . '</td>'
            . '<td>' . $line . '</td>'
            . '</tr>';
      }

      echo '</tbody>'
         . '</table>'
         . '</div>'
         . '</div>';

      $trace = debug_backtrace();
      array_shift($trace);
      Log::error($errstr, $trace);
   }

   spl_autoload_register('alo_autoloader');
   set_error_handler('alo_error_handler', ini_get('error_reporting'));

   require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'alo.php';
   Alo::$router = new Alo\Controller\Router();
   Alo::$router->init();