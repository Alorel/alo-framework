<?php

   namespace Alo\CLI;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Handles input & output
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      abstract class IO {

         /**
          * Arguments passed on to PHP excl the first (file name)
          *
          * @var array
          */
         public static $argv;

         /**
          * Clears previous output
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param int $lines Amount of empty lines to output
          */
         static function echoLines($lines = 100) {
            $l     = "";
            $lines = (int)$lines;

            for($i = 0; $i < $lines; $i++) {
               $l .= PHP_EOL;
            }

            echo $l;
         }

         /**
          * Opens a file using the default program. Works on Windows Linux as long as xdg-utils are installed.
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $path File path.
          */
         static function openFileDefault($path) {
            if(serverIsWindows()) {
               shell_exec('start "' . $path . '"');
            } else {
               shell_exec('xdg-open "' . $path . '"');
            }
         }

         /**
          * Reads a line of user input. Windows does not have readline() so a cross-platform solution is required.
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $prompt Prompt message
          *
          * @return string
          */
         static function readline($prompt = null) {
            if($prompt) {
               echo $prompt;
            }

            $r = trim(strtolower(stream_get_line(STDIN, PHP_INT_MAX, PHP_EOL)));
            echo PHP_EOL;

            return $r;
         }

      }

      IO::$argv = get($_SERVER['argv']);
      if(is_array(IO::$argv)) {
         array_shift(IO::$argv);
      } else {
         IO::$argv = [];
      }
   }
