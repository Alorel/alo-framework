<?php

   namespace Controller;

   use Alo\Controller\AbstractController;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * A sample controller
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      class Sample extends AbstractController {

         /**
          * Default index page
          *
          * @author Art <a.molcanovas@gmail.com>
          */
         function index() {
            $this->loadView('sample', ['foo' => 'bar']);
         }

         /**
          * Sample method for a more complex route
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $first  The first string to echo
          * @param string $second The second string to echo
          */
         function echoer($first = '[not supplied]', $second = '[not supplied]') {
            echo 'Your first param was ' . $first . ' and your second was ' . $second;
         }

         /**
          * Sample method for when the class parameter isn't supplied.
          *
          * @author Art <a.molcanovas@gmail.com>
          */
         function noclass() {
            echo 'You\'re in the noclass method! Your routed args are ' . implode(', ', func_get_args());
         }

         /**
          * Sample method for the final route test
          *
          * @author Art <a.molcanovas@gmail.com>
          */
         function noparam() {
            echo 'You\'re in the no-param method!';
         }

         /**
          * Sample method for showing parameters
          *
          * @author Art <a.molcanovas@gmail.com>
          */
         function paramed() {
            $vars = func_get_args();
            echo 'Your path params are' . ($vars ? ' ' . implode(', ', $vars) : '... not set.');
         }
      }
   }
