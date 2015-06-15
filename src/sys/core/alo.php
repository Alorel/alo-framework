<?php

   if(!defined('GEN_START')) {
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
       * SFTP connection manager
       *
       * @var Alo\SFTP
       */
      static $sftp;

      /**
       * Code profiler
       *
       * @var Alo\Profiler
       */
      static $profiler;

      /**
       * File manager
       *
       * @var Alo\File
       */
      static $file;

      /**
       * Email manager
       *
       * @var Alo\Email
       */
      static $email;

      /**
       * Object-oriented cURL wrapper
       *
       * @var Alo\cURL
       */
      static $curl;

      /**
       * Crontab manager
       *
       * @var Alo\Cron
       */
      static $cron;

      /**
       * HTML form validator
       *
       * @var Alo\Validators\Form
       */
      static $form_validator;

      /**
       * Database connection
       *
       * @var Alo\Db\AbstractDb
       */
      static $db;

      /**
       * Cache instance
       *
       * @var Alo\Cache\AbstractCache
       */
      static $cache;

      /**
       * The session handler
       *
       * @var Alo\Session\AbstractSession
       */
      static $session;

      /**
       * The loaded controller
       *
       * @var Alo\Controller\AbstractController
       */
      static $controller;

      /**
       * The routing class
       *
       * @var Alo\Controller\Router
       */
      static $router;

      /**
       * Windows service handler
       *
       * @var Alo\Windows\Service
       */
      static $service;

      /**
       * Download manager
       *
       * @var Alo\CLI\Downloader
       */
      static $downloader;

      /**
       * Locale manager
       *
       * @var Alo\Locale
       */
      static $locale;

      /**
       * Loads a session
       *
       * @param string $type The session class name - see Alo::SESS_* constants
       *
       * @return Alo\Session\AbstractSession
       * @see self::SESS_MYSQL
       * @see self::SESS_MEMCACHED
       */
      static function loadSession($type = self::SESS_MYSQL) {
         if(!self::$session) {
            $sess          = '\Alo\Session\\' . $type;
            self::$session = new $sess();
         }

         return self::$session;
      }

      /**
       * Loads a configuration file based on environment: from DIR_SYS/config during setup & DIR_APP/config during
       * production/development
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $path        The config file relative path without the file extension, e.g. to load a file found
       *                            in config/db/mysql.php provide db/mysql
       * @param bool   $return_path If set to true it will return the calculated path instead of requiring the file
       *
       * @return string|bool The path is $return_path is true, TRUE if it is false
       */
      static function loadConfig($path, $return_path = false) {
         $dir  =
            (defined('ENVIRONMENT') && ENVIRONMENT === ENV_SETUP ? DIR_SYS : DIR_APP) . 'config' . DIRECTORY_SEPARATOR;
         $path = strtolower($path);
         if(substr($path, -4) == '.php') {
            $path = substr($path, 0, -4);
         }

         $final_path = '';
         if(file_exists($dir . $path . '.php')) {
            $final_path = $dir . $path . '.php';
         } else {
            trigger_error('Configuration file ' .
                          $path .
                          ' not found in the application folder. Attempting to load from sys.',
                          E_USER_WARNING);
            $final_path = DIR_SYS . 'config' . DIRECTORY_SEPARATOR . $path . '.php';
         }

         if($return_path) {
            return $final_path;
         } else {
            include_once $final_path;

            return true;
         }
      }
   }

   \Log::debug('Alo framework class initialised');