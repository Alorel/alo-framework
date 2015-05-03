<?php

   namespace Alo\Test;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * Tests functions for output or return values
    *
    * @author  Art <a.molcanovas@gmail.com>
    * @package TestingSuite
    */
   class FunctionTester extends AbstractTester {

      /**
       * Returns the callable parameter for call_user_func_array()
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $name Method name
       * @return string
       */
      protected function getCallable($name) {
         return $name;
      }
   }