<?php

   namespace Alo\Db;

   use Alo;
   use Alo\Cache\AbstractCache;
   use PDOStatement;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * The framework database interface
       *
       * @author  Art <a.molcanovas@gmail.com>
       * @package Database
       */
      abstract class AbstractDb {

         /**
          * Defines a parameter as "whether to cache"
          *
          * @var string
          */
         const V_CACHE = 'c';

         /**
          * Defines a parameter as "cache time" in seconds
          *
          * @var string
          */
         const V_TIME = 't';

         /**
          * Defines a parameter as "cache hash prefix"
          *
          * @var string
          */
         const V_PREFIX = 'p';

         /**
          * Defines a variable as "whether to fetch as a numeric array instead of
          * assoc"
          *
          * @var string
          */
         const V_FETCH_NUM = 'n';
         /**
          * Static reference to the last instance of the class
          *
          * @var AbstractDb
          */
         static $this;
         /**
          * Default query options
          *
          * @var array
          */
         protected static $default_settings = [
            self::V_CACHE     => false,
            self::V_TIME      => 300,
            self::V_PREFIX    => null,
            self::V_FETCH_NUM => false
         ];
         /**
          * The cache object in use
          *
          * @var AbstractCache
          */
         protected $cache;
         /**
          * The prefix to use for cache keys
          *
          * @var string
          */
         protected $cache_prefix;
         /**
          * The last cache hash generated
          *
          * @var string
          */
         protected $last_hash;

         /**
          * Instantiates the database connection
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $cache Which cache interface to use
          */
         function __construct($cache) {
            if(!\Alo::$cache) {
               $this->cache = new $cache;
            } else {
               $this->cache = &\Alo::$cache;
            }

            if(!Alo::$db) {
               Alo::$db = &$this;
            }

            self::$this = &$this;
         }

         /**
          * Returns the last hash generated
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getLastHash() {
            return $this->last_hash;
         }

         /**
          * Prepares a statement
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $sql A SQL statement to prepare
          *
          * @return PDOStatement
          */
         abstract function prepare($sql);

         /**
          * Checks whether a transaction is active
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return boolean
          */
         abstract function transactionActive();

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
         abstract function aggregate($sql, $params = null, array $settings = []);

         /**
          * Begins a transaction
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return AbstractDb
          */
         abstract function beginTransaction();

         /**
          * Rolls back a transaction
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return AbstractDb
          */
         abstract function rollBack();

         /**
          * Commits a transaction
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return AbstractDb
          */
         abstract function commit();

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
         abstract function prepQuery($sql, $params = null, array $settings = []);

         /**
          * Executes a quick unescaped query without preparing it
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $sql SQL code
          *
          * @return array|boolean Result array on SELECT queries, TRUE/FALSE for others
          */
         abstract function query($sql);

         /**
          * Creates a query hash for caching
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $sql    QUery string
          * @param array  $params Query parameters
          * @param string $prefix Optional prefix
          *
          * @return string An MD5 hash
          */
         protected function hash($sql, $params, $prefix = null) {
            $this->last_hash = $this->cache_prefix . md5($prefix . $sql . json_encode($params));

            return $this->last_hash;
         }
      }
   }