<?php

   namespace Alo\Db;

   use Exception;
   use PDO;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   \Alo::loadConfig('db' . DIRECTORY_SEPARATOR . 'mysql');

   /**
    * MySQL database manager
    *
    * @author Art <a.molcanovas@gmail.com>
    * @author Art <a.molcanovas@gmail.com>
    */
   class MySQL extends AbstractDb {

      /**
       * The PDO instance
       *
       * @var PDO
       */
      protected $pdo;

      /**
       * Instantiates the database connection
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $ip      The IP address to use
       * @param int    $port    The port to use
       * @param string $user    The username
       * @param string $pw      The password
       * @param string $db      The database to use
       * @param string $cache   Which cache interface to use
       * @param array  $options Connection options
       */
      function __construct($ip = ALO_MYSQL_SERVER, $port = ALO_MYSQL_PORT, $user = ALO_MYSQL_USER, $pw = ALO_MYSQL_PW, $db = ALO_MYSQL_DATABASE, $cache = ALO_MYSQL_CACHE, array $options = null) {
         $this->pdo = new PDO('mysql:dbname=' . $db . ';host=' . $ip . ';port=' . $port, $user, $pw, $options);

         $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
         $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

         $this->cache_prefix = ALO_MYSQL_CACHE_PREFIX;
         parent::__construct($cache);
         \Log::debug('Initialised MySQL database connection');
      }

      function aggregate($sql, $params = null, array $settings = []) {
         $settings = \array_merge(self::$default_settings, $settings);
         $hash = $this->hash($sql, $params, $settings[self::V_PREFIX]);
         $cache = $settings[self::V_CACHE];

         if ($settings[self::V_CACHE] && $get = $this->cache->get($hash)) {
            return $get;
         } else {
            $settings[self::V_FETCH_NUM] = true;
            $settings[self::V_CACHE] = false;
            $prep = $this->prepQuery($sql, $params, $settings);
            $result = null;

            if ($prep) {
               $result = strpos($prep[0][0], '.') === false ? (int)$prep[0][0] : (float)$prep[0][0];
            }

            if ($cache) {
               $this->cache->set($hash, $result);
            }

            return $result;
         }
      }

      function beginTransaction() {
         return $this->pdo->inTransaction() ? true : $this->pdo->beginTransaction();
      }

      function commit() {
         return $this->pdo->inTransaction() ? $this->pdo->commit() : true;
      }

      function prepQuery($sql, $params = null, array $settings = []) {
         $settings = \array_merge(self::$default_settings, $settings);
         $hash = $this->hash($sql, $params, $settings[self::V_PREFIX]);

         if (stripos($sql, 'insert into') !== false || stripos($sql, 'replace into') !== false) {
            $settings[self::V_CACHE] = false;
         }

         if ($settings[self::V_CACHE] && $get = $this->cache->get($hash)) {
            return $get;
         } else {
            $pdo = $this->pdo->prepare($sql);
            $exec = $pdo->execute($params);
            $res = stripos($sql, 'select') !== false ? $pdo->fetchAll($settings[self::V_FETCH_NUM] ? PDO::FETCH_NUM : PDO::FETCH_ASSOC) : $exec;

            if ($settings[self::V_CACHE]) {
               $this->cache->set($hash, $res, $settings[self::V_TIME]);
            }

            return $res;
         }
      }

      function query($sql) {
         $s = $this->pdo->query($sql);

         return stripos($sql, 'select') !== false ? $s->fetchAll(PDO::FETCH_ASSOC) : $s !== false;

      }

      function rollBack() {
         return $this->pdo->inTransaction() ? $this->pdo->rollBack() : true;
      }

      function prepare($sql) {
         return $this->pdo->prepare($sql);
      }

      function transactionActive() {
         return $this->pdo->inTransaction();
      }

      /**
       * Handles direct calls to PDO
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $name      Method name
       * @param array  $arguments Array of parameters
       * @return mixed
       */
      function __call($name, $arguments) {
         return call_user_func_array([$this->pdo, $name], $arguments);
      }

   }