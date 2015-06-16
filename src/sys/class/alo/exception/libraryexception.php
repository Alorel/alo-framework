<?php

   namespace Alo\Exception;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Library-related exceptions
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      class LibraryException extends AbstractException {

         /**
          * Code when a required library is not loaded
          *
          * @var int
          */
         const E_REQUIRED_LIB_NOT_FOUND = 101;
      }
   }
