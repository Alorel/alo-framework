<?php

   namespace Alo\Exception;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * PHP extension-related exceptions
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   class ExtensionException extends AbstractException {

      /**
       * Code when a PHP extension is not loaded
       *
       * @var int
       */
      const E_EXT_NOT_LOADED = 100;

   }