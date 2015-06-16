<?php

   namespace Alo\Exception;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Abstract framework exception
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      abstract class AbstractException extends \Exception {

         /**
          * Creates the exception
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string     $message  Exception message
          * @param int        $code     Exception code
          * @param \Exception $previous Previous exception, if chaining
          */
         function __construct($message = '', $code = 0, $previous = null) {
            parent::__construct($message, $code, $previous);

            \Log::error($message . ' (trace: ' . $this->getTraceAsString() . ')');
         }

      }
   }
   