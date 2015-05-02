<?php

   use Alo\Cache\AbstractCache;
   use Alo\Controller\AbstractController;
   use Alo\Controller\Router;
   use Alo\Db\AbstractDb;
   use Alo\Session\AbstractSession;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * The global framework class
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   class Alo {

      /**
       * Defines a session type as SQL
       *
       * @var string
       */
      const SESS_MYSQL = 'SQLSession';

      /**
       * Defines a session type as Memcached
       *
       * @var string
       */
      const SESS_MEMCACHED = 'MemcachedSession';

      /**
       * Database connection
       *
       * @var AbstractDb
       */
      static $db;

      /**
       * Cache instance
       *
       * @var AbstractCache
       */
      static $cache;

      /**
       * The session handler
       *
       * @var AbstractSession
       */
      static $session;

      /**
       * The loaded controller
       *
       * @var AbstractController
       */
      static $controller;

      /**
       * The routing class
       *
       * @var Router
       */
      static $router;

      /**
       * Loads a session
       *
       * @param string $type The session class name - see Alo::SESS_* constants
       * @return AbstractSession
       * @see self::SESS_MYSQL
       * @see self::SESS_MEMCACHED
       */
      static function loadSession($type = self::SESS_MYSQL) {
         if (!self::$session) {
            $sess = '\Alo\Session\\' . $type;
            self::$session = new $sess();
         }

         return self::$session;
      }

      /**
       * Loads a configuration file based on environment: from DIR_SYS/config during setup & DIR_APP/config during
       * production/development
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $path        The config file relative path without the file extension, e.g. to load a file found
       *                            in config/db/mysql.php provide db/mysql
       * @param bool   $return_path If set to true it will return the calculated path instead of requiring the file
       * @return string|bool The path is $return_path is true, TRUE if it is false
       */
      static function loadConfig($path, $return_path = false) {
         $dir = (defined('ENVIRONMENT') && ENVIRONMENT === ENV_SETUP ? DIR_SYS : DIR_APP) . 'config' . DIRECTORY_SEPARATOR;
         $path = strtolower($path);
         if (substr($path, -4) == '.php') {
            $path = substr($path, 0, -4);
         }

         $final_path = '';
         if (file_exists($dir . $path . '.php')) {
            $final_path = $dir . $path . '.php';
         } else {
            trigger_error('Configuration file ' . $path . ' not found in the application folder. Attempting to load from sys.', E_USER_WARNING);
            $final_path = DIR_SYS . 'config' . DIRECTORY_SEPARATOR . $path . '.php';
         }

         if ($return_path) {
            return $final_path;
         } else {
            return true;
         }
      }
   }

   \Log::debug('Alo framework class initialised');