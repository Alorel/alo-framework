<?php

   namespace Controller;

   use Alo\Controller\AbstractErrorController;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * A sample error controller
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      class SampleErrorController extends AbstractErrorController {

         /**
          * Displays the error page
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param int    $code    The error HTTP code
          * @param string $message Optional message override
          */
         function error($code = 404, $message = null) {
            http_response_code((int)$code);
            $dir  = defined('ENVIRONMENT') && ENVIRONMENT === ENV_SETUP ? DIR_SYS : DIR_APP;
            $path = $dir . 'error' . DIRECTORY_SEPARATOR . $code . '.html';

            if(file_exists($path)) {
               include $path;
            } else {
               $this->displayErrorPage($code);
            }
         }

         /**
          * Displays a generic error page for which there is no HTML file
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param int $code The HTTP response code
          */
         function displayErrorPage($code = 404) {
            $code = (int)$code;
            echo '<!DOCTYPE html>'
                 . '<html>'
                 . '<head>'
                 . '<title>Uh-oh... ' . $code . '</title>'
                 . '<meta charset="UTF-8">'
                 . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
                 . '</head>'
                 . '<body>'
                 . '<div>' . $code . ' error page</div>'
                 . '</body>'
                 . '</html>';
         }

      }
   }
   