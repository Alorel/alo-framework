<?php

   ob_start();
   include_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'index.php';

   define('PHPUNIT_ROOT', __DIR__ . DIRECTORY_SEPARATOR);

   define('PHPUNIT_RUNNING', true);

   function _load_classes($dirName) {
      $di = new DirectoryIterator($dirName);
      foreach($di as $file) {
         if($file->isDir() && !$file->isLink() && !$file->isDot()) {
            // recurse into directories other than a few special ones
            _load_classes($file->getPathname());
         } elseif(substr($file->getFilename(), -4) === '.php') {
            // save the class name / path of a .php file found
            include_once $file->getPathname();
         }
      }
   }

   function _unit_dump($data) {
      ob_start();
      var_dump($data);

      return ob_get_clean();
   }

   // ========== Autoload classes ==========
   _load_classes(DIR_SYS . 'core');
   _load_classes(DIR_SYS . 'class');
   _load_classes(DIR_APP . 'class');
   _load_classes(DIR_APP . 'interface');
   _load_classes(DIR_APP . 'traits');

   Alo::$router = new \Alo\Controller\Router();
   ob_end_clean();

   include __DIR__ . '/vendor/autoload.php';

   abstract class PHPUNIT_GLOBAL {
      /** @var \Alo\Cron */
      static $cron;

      /** @var \Alo\Cache\MemcachedWrapper */
      static $mcWrapper;

      /** @var \Alo\Cache\RedisWrapper */
      static $redisWrapper;

      /** @var \Alo\Session\MemcachedSession */
      static $mcSession;

      /** @var \Alo\Session\RedisSession */
      static $redisSession;

      /** @var  \Alo\Db\MySQL */
      static $mysql;

      /** @var  \Alo\Session\MySQLSession */
      static $mysqlsession;
   }

   if(!server_is_windows()) {
      PHPUNIT_GLOBAL::$cron = new \Alo\Cron();
   }

   PHPUNIT_GLOBAL::$mcWrapper    = new \Alo\Cache\MemcachedWrapper();
   PHPUNIT_GLOBAL::$redisWrapper = new \Alo\Cache\RedisWrapper();
   PHPUNIT_GLOBAL::$mcSession    = new \Alo\Session\MemcachedSession(PHPUNIT_GLOBAL::$mcWrapper);
   PHPUNIT_GLOBAL::$redisSession = new \Alo\Session\RedisSession(PHPUNIT_GLOBAL::$redisWrapper);
   PHPUNIT_GLOBAL::$mysql        = new \Alo\Db\MySQL();

   PHPUNIT_GLOBAL::$mysql->prepQuery('CREATE TABLE IF NOT EXISTS `alo_session` (`id`     CHAR(128)
           CHARACTER SET `ascii` NOT NULL,
  `data`   VARCHAR(16000)      NOT NULL,
  `access` TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =`utf8mb4`;');

   PHPUNIT_GLOBAL::$mysqlsession = new \Alo\Session\MySQLSession(PHPUNIT_GLOBAL::$mysql);
