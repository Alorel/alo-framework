<?php

   if(!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * The default Redis IP to use
    *
    * @var string
    */
   define('ALO_REDIS_IP', '127.0.0.1');

   /**
    * The default Memcached port to use
    *
    * @var int
    */
   define('ALO_REDIS_PORT', 6379);