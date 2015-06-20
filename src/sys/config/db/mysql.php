<?php

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * The default database server IP/host to use
       *
       * @var string
       */
      define('ALO_MYSQL_SERVER', '127.0.0.1');

      /**
       * The default database port to use
       *
       * @var int
       */
      define('ALO_MYSQL_PORT', 3306);
      /**
       * The default database to connect to
       *
       * @var string
       */
      define('ALO_MYSQL_DATABASE', 'your-database-name');
      /**
       * The default username to use
       *
       * @var string
       */
      define('ALO_MYSQL_USER', 'root');
      /**
       * The default password to use
       *
       * @var string
       */
      define('ALO_MYSQL_PW', '');

      /**
       * Which caching class to use. These are found in sys/class/alo/cache
       *
       * @var string
       */
      define('ALO_MYSQL_CACHE', '\Alo\Cache\RedisWrapper');

      /**
       * The prefix to use for DB cache keys
       *
       * @var string
       */
      define('ALO_MYSQL_CACHE_PREFIX', 'mysql_');

      /**
       * Connection charset for MySQL
       *
       * @var string
       */
      define('ALO_MYSQL_CHARSET', 'utf8mb4');
   }
