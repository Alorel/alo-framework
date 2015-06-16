<?php

   namespace Alo\Controller;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * The abstract error controller. Your custom error controllers will need to follow this design.
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      abstract class AbstractErrorController extends AbstractController {

         /**
          * Displays the error page
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param int    $code    The error HTTP response code
          * @param string $message Optional message override
          */
         abstract function error($code = 404, $message = null);

         /**
          * Displays a generic error page for which there is no HTML file
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param int $code The HTTP response code
          */
         abstract function displayErrorPage($code = 404);
      }
   }
