<?php

   namespace Alo\FileSystem;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * The abstract file system class
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      abstract class AbstractFileSystem {

         /**
          * Static reference to the last instance of the class
          *
          * @var AbstractFileSystem
          */
         static $this;
         /**
          * Replacements for placeholders
          *
          * @var array
          */
         protected $replace;

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          */
         function __construct() {
            $time          = time();
            $this->replace = [
               'search'  => [
                  '{timestamp}',
                  '{datetime}',
                  '{date}',
                  '{time}',
                  '{year}',
                  '{month}',
                  '{day}',
                  '{weekday}'
               ],
               'replace' => [
                  $time,
                  date('Y-m-d H.i.s', $time),
                  date('Y-m-d', $time),
                  date('H.i.s', $time),
                  date('Y', $time),
                  date('m', $time),
                  date('d', $time),
                  date('l', $time)
               ]
            ];

            self::$this = &$this;
         }

         /**
          * Perform placeholder replacement operations
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $subject The string to perform operations in
          */
         protected function replace(&$subject) {
            $subject = str_ireplace($this->replace['search'], $this->replace['replace'], $subject);
         }
      }
   }