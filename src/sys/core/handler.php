<?php

   namespace Alo;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * Handles autoloading, errors and exceptions
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   abstract class Handler {

      /**
       * The error handler
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param int    $errno   The level of the error raised
       * @param string $errstr  The error message
       * @param string $errfile The filename that the error was raised in
       * @param int    $errline The line number the error was raised at
       */
      static function error($errno, $errstr, $errfile, $errline) {
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
            . '<div>Backtrace:</div>';

         $trace = array_reverse(debug_backtrace());
         array_pop($trace);

         self::echo_trace($trace);

         echo '</div>'
            . '</div>';

         $trace = \debug_backtrace();
         array_shift($trace);
         \Log::error($errstr, $trace);
      }

      /**
       * Used to automatically load class,interface and trait files
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $name Class name
       */
      static function autoloader($name) {
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
       * Echoes previous exceptions if applicable
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param null|\Exception $e The previous exception
       */
      protected static function echo_previous_exceptions($e) {
         if ($e instanceof \Exception) {
            echo '<div></div>Preceded by <span style="font-weight: bold">'
               . $e->getCode() . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . '\'s line ' . $e->getLine()
               . '.</span>';

            self::echo_previous_exceptions($e->getPrevious());
         }
      }

      /**
       * Echoes the debug backtrace
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param array $trace The backtrace
       */
      protected static function echo_trace($trace) {
         echo '<table cellpadding="2" border="1" style="border-collapse:collapse;width:100%;text-align:center">'
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
            . '</table>';
      }

      /**
       * Exception handler
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param \Exception $e The exception
       */
      static function ecxeption(\Exception $e) {
         $msg = $e->getMessage();
         $trace = $e->getTrace();
         array_pop($trace);

         echo '<div style="text-align:center;margin:12px auto 12px auto">'
            . '<div style="text-align:left;display:inline-block;padding:2px;background:#FD8C7F;border:2px solid #F00;color:#000">'
            . '<div style="font-weight:bold;margin-bottom:1em">'
            . $e->getCode() . ' : ' . $e->getMessage();

         self::echo_previous_exceptions($e->getPrevious());

         echo '</div>'
            . '<div>Raised in <span style="font-weight:bold">' . $e->getFile() . ': ' . $e->getLine() . '</span></div>'
            . '<div>Backtrace:</div>';

         self::echo_trace($trace);

         echo '</div>'
            . '</div>';

         $trace = $e->getTrace();
         array_shift($trace);
         Log::error($msg, $trace);
      }
   }