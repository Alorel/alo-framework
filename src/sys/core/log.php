<?php

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   } elseif (!defined('LOG_LEVEL') || !defined('LOG_LEVEL_ERROR') || !defined('LOG_LEVEL_DEBUG') || !defined('LOG_LEVEL_NONE') || !in_array(LOG_LEVEL, [
         LOG_LEVEL_ERROR,
         LOG_DEBUG,
         LOG_LEVEL_NONE
      ], true)
   ) {
      http_response_code(500);
      die('Invalid LOG_LEVEL setting. Valid values are the framework constants LOG_LEVEL_ERROR, LOG_LEVEL_DEBUG and LOG_LEVEL_NONE!');
   }

   /**
    * Logs messages
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   abstract class Log {

      /**
       * Identifier for error messages
       *
       * @var string
       */
      const MSG_ERROR = 'ERROR';

      /**
       * Identifier for debug messages
       *
       * @var string
       */
      const MSG_DEBUG = 'DEBUG';

      /**
       * The logging level
       *
       * @var string
       */
      protected static $log_level = LOG_LEVEL;

      /**
       * Today's date
       *
       * @var string
       */
      protected static $today;

      /**
       * Gets or sets the log level. If no parameter is passed, returns
       * self::$log_level, if it's set and has a valid value, sets it and
       * returns TRUE;
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string|null $level The logging level
       * @return boolean|string
       * @see    self::$log_level
       */
      static function log_level($level = null) {
         if (in_array($level, [
            LOG_DEBUG,
            LOG_LEVEL_ERROR,
            LOG_LEVEL_NONE
         ], true)) {
            self::$log_level = $level;

            return true;
         } else {
            return self::$log_level;
         }
      }

      /**
       * Logs a debug level message
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $msg   The message
       * @param array  $trace optionally, supply the debug trace
       * @return string The message you passed on
       */
      static function debug($msg, $trace = null) {
         if (self::$log_level === LOG_DEBUG && is_scalar($msg)) {
            self::do_write($msg, self::MSG_DEBUG, $trace);
         }

         return $msg;
      }

      /**
       * Logs a error level message
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $msg   The message
       * @param array  $trace optionally, supply the debug trace
       * @return string The message you passed on
       */
      static function error($msg, $trace = null) {
         if (self::$log_level != LOG_LEVEL_NONE && is_scalar($msg)) {
            self::do_write($msg, self::MSG_ERROR, $trace);
         }

         return $msg;
      }

      /**
       * Performs the write operation
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $msg   The message to log
       * @param string $level The level of the message
       * @param array  $trace optionally, supply the debug trace
       * @return boolean
       */
      protected static function do_write($msg, $level, $trace = null) {
         $filepath = DIR_APP . 'logs' . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';
         $fp = @fopen($filepath, 'ab');

         if (!$fp) {
            return false;
         } else {
            if (!$trace || !is_array($trace)) {
               $trace = debug_backtrace();
               array_shift($trace);
            }

            if (defined('LOG_INTENSE') && LOG_INTENSE) {
               $trace_append = serialize($trace);
            } else {
               $xpl = explode(DIR_INDEX, $trace[0]['file']);

               $trace_append = $trace[0]['line'] . ' @ "'
                  . str_replace('"', '\"', isset($xpl[1]) ? $xpl[1] : $xpl[0]) . '"';
            }

            $message = str_pad('[' . timestamp_precise() . ']', 25, ' ')
               . ' ' . str_pad($level, 5, ' ') . ' | "'
               . str_replace('"', '\"', $msg) . '" | ' . $trace_append . PHP_EOL;

            flock($fp, LOCK_EX);
            fwrite($fp, $message);
            flock($fp, LOCK_UN);
            fclose($fp);

            @chmod($filepath, '0666');

            return true;
         }
      }

   }