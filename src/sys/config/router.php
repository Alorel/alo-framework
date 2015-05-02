<?php

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

//Controller class called for error page handling
   $error_controller_class = '\Alo\Controller\Error';
   $default_controller = 'sample';

//Routes array
   $routes = [
      'foo/([a-z\s]+)' => 'foo/$1'
   ];