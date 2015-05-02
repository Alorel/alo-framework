<?php

   namespace Alo\Exception;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * Cron-related exceptions
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   class CronException extends AbstractException {

      /**
       * Code when the minute expression is invalid
       *
       * @var int
       */
      const E_INVALID_MIN = 101;

      /**
       * Code when the schedule expression is invalid
       *
       * @var int
       */
      const E_INVALID_EXPR = 102;

      /**
       * Code when one or more arguments are non-scalar
       *
       * @var int
       */
      const E_ARGS_NONSCALAR = 103;

   }