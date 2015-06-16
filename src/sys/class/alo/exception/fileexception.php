<?php

   namespace Alo\Exception;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * File-related exceptions
       *
       * @author     Art <a.molcanovas@gmail.com>
       * @deprecated Since 1.1. Please use FileSystemException.
       */
      class FileException extends FileSystemException {
      }
   }
   