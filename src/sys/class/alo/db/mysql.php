<?php

   namespace Alo\Db;

   use PDO;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

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
          *
          * @param string $ip      The IP address to use
          * @param int    $port    The port to use
          * @param string $user    The username
          * @param string $pw      The password
          * @param string $db      The database to use
          * @param string $cache   Which cache interface to use
          * @param array  $options Connection options
          */
         function __construct($ip = ALO_MYSQL_SERVER,
                              $port = ALO_MYSQL_PORT,
                              $user = ALO_MYSQL_USER,
                              $pw = ALO_MYSQL_PW,
                              $db = ALO_MYSQL_DATABASE,
                              $cache = ALO_MYSQL_CACHE,
                              array $options = null) {

            $this->pdo = new PDO('mysql:dbname=' . $db . ';host=' . $ip . ';charset=' . ALO_MYSQL_CHARSET . ';port=' . $port, $user, $pw, $options);

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $this->cachePrefix = ALO_MYSQL_CACHE_PREFIX;
            parent::__construct($cache);
            \Log::debug('Initialised MySQL database connection');
         }

         /**
          * Instantiates the database connection
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $ip      The IP address to use
          * @param int    $port    The port to use
          * @param string $user    The username
          * @param string $pw      The password
          * @param string $db      The database to use
          * @param string $cache   Which cache interface to use
          * @param array  $options Connection options
          *
          * @return MySQL
          */
         static function mysql($ip = ALO_MYSQL_SERVER,
                               $port = ALO_MYSQL_PORT,
                               $user = ALO_MYSQL_USER,
                               $pw = ALO_MYSQL_PW,
                               $db = ALO_MYSQL_DATABASE,
                               $cache = ALO_MYSQL_CACHE,
                               array $options = null) {
            return new MySQL($ip, $port, $user, $pw, $db, $cache, $options);
         }

         /**
          * Returns an aggregated one-column result set, e.g. from a count(*) query
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $sql      The SQL code
          * @param array  $params   Bound parameters
          * @param array  $settings Optional settings
          *
          * @return int|float
          */
         function aggregate($sql, $params = null, array $settings = []) {
            $settings = \array_merge(self::$defaultSettings, $settings);
            $hash     = $this->hash($sql, $params, $settings[self::V_PREFIX]);
            $cache    = $settings[self::V_CACHE];

            if($settings[self::V_CACHE] && $get = $this->cache->get($hash)) {
               return $get;
            } else {
               $settings[self::V_FETCH_NUM] = true;
               $settings[self::V_CACHE]     = false;
               $prep                        = $this->prepQuery($sql, $params, $settings);
               $result                      = null;

               if($prep) {
                  $result = strpos($prep[0][0], '.') === false ? (int)$prep[0][0] : (float)$prep[0][0];
               }

               if($cache) {
                  $this->cache->set($hash, $result);
               }

               return $result;
            }
         }

         /**
          * Executes a prepared query and returns the results
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $sql      The SQL code
          * @param array  $params   Bound parameters
          * @param array  $settings Optional settings
          *
          * @return array|boolean Result array on SELECT queries, TRUE/FALSE for others
          */
         function prepQuery($sql, $params = null, array $settings = []) {
            $settings = \array_merge(self::$defaultSettings, $settings);
            $hash     = $this->hash($sql, $params, $settings[self::V_PREFIX]);

            if(stripos($sql, 'insert into') !== false || stripos($sql, 'replace into') !== false) {
               $settings[self::V_CACHE] = false;
            }

            if($settings[self::V_CACHE] && $get = $this->cache->get($hash)) {
               return $get;
            } else {
               $pdo  = $this->pdo->prepare($sql);
               $exec = $pdo->execute($params);
               $res  =
                  stripos($sql, 'select') !== false ?
                     $pdo->fetchAll($settings[self::V_FETCH_NUM] ? PDO::FETCH_NUM : PDO::FETCH_ASSOC) : $exec;

               if($settings[self::V_CACHE]) {
                  $this->cache->set($hash, $res, $settings[self::V_TIME]);
               }

               return $res;
            }
         }

         /**
          * Begins a transaction
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return MySQL
          */
         function beginTransaction() {
            return $this->pdo->inTransaction() ? true : $this->pdo->beginTransaction();
         }

         /**
          * Commits a transaction
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return MySQL
          */
         function commit() {
            return $this->pdo->inTransaction() ? $this->pdo->commit() : true;
         }

         /**
          * Prepares a statement
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $sql A SQL statement to prepare
          *
          * @return \PDOStatement
          */
         function prepare($sql) {
            return $this->pdo->prepare($sql);
         }

         /**
          * Executes a quick unescaped query without preparing it
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $sql SQL code
          *
          * @return array|boolean Result array on SELECT queries, TRUE/FALSE for others
          */
         function query($sql) {
            $s = $this->pdo->query($sql);

            return stripos($sql, 'select') !== false ? $s->fetchAll(PDO::FETCH_ASSOC) : $s !== false;

         }

         /**
          * Rolls back a transaction
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return MySQL
          */
         function rollBack() {
            return $this->pdo->inTransaction() ? $this->pdo->rollBack() : true;
         }

         /**
          * Checks whether a transaction is active
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return boolean
          */
         function transactionActive() {
            return $this->pdo->inTransaction();
         }

         /**
          * Handles direct calls to PDO
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $name      Method name
          * @param array  $arguments Array of parameters
          *
          * @return mixed
          */
         function __call($name, $arguments) {
            return call_user_func_array([$this->pdo, $name], $arguments);
         }

      }
   }
