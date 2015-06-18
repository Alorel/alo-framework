<?php

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      //Controller called for error page handling
      $errorControllerClass = 'SampleErrorController';

      //The default controller if one isn't supplied
      $defaultController = 'sample';

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
         'sample/([^/]+)/([^/]+)/?'      => [
            'method' => 'noclass',
            'args'   => ['hardcoded', '$2']
         ]
      ];
   }
