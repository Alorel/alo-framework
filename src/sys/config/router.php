<?php

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   //Controller called for error page handling
   $error_controller_class = 'SampleErrorController';

   //The default controller if one isn't supplied
   $default_controller = 'sample';

   //Routes array
   $routes = [
      'cart/checkout'                 => [
         'dir'    => 'sample',
         'class'  => 'cart',
         'method' => 'checkout'
      ],
      'sample-me/?([^/]*)/?([^/]*)/?' => [
         'class'  => 'sample',
         'method' => 'echoer',
         'args'   => ['$1', '$2']
      ],
      'sample/([^/]+)/([^/]+)/?'        => [
         'method' => 'noclass',
         'args'   => ['hardcoded', '$2']
      ]
   ];