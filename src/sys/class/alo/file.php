<?php

   namespace Alo;

   if(!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * Object-oriented file handler
    *
    * @author     Arturas Molcanovas <a.molcanovas@gmail.com>
    * @deprecated Since 1.1. Please use \Alo\FileSystem\File.
    */
   class File extends \Alo\FileSystem\File {

   }