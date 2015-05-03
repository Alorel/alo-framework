<?php

   namespace Alo\Exception;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * Profiler-related exceptions
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   class ProfilerException extends AbstractException {

      /**
       * Code when a referenced mark is not found
       *
       * @var int
       */
      const E_MARK_NOT_SET = 100;

      /**
       * Code when a specified key is invalid
       *
       * @var int
       */
      const E_KEY_INVALID = 101;
   }