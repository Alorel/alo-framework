<?php

   namespace Alo;

   use Alo\FileSystem\File as NewFile;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Object-oriented file handler
       *
       * @author     Arturas Molcanovas <a.molcanovas@gmail.com>
       * @deprecated Since 1.1. Please use \Alo\FileSystem\File.
       */
      class File extends NewFile {

      }
   }
