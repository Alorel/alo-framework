<?php

   namespace Alo\Exception;

   if(!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * Operating system-related exceptions
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   class OSException extends AbstractException {

      /**
       * Code when the operation is not supported on the operating system
       *
       * @var int
       */
      const E_UNSUPPORTED = 101;

   }