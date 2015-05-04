<?php

   namespace Alo\Cache;

   use Memcache;
   use Memcached;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   \Alo::loadConfig('memcached');

   /**
    * A wrapper for PHP's Memcached extension. Will try to use the Memcached class
    * first, if it doesn't exist, will use Memcache.
    *
    * @author  Art <a.molcanovas@gmail.com>
    * @package Cache
    */
   class MemcachedWrapper extends AbstractCache {

      /**
       * Defines the class as Memcached
       *
       * @var int
       */
      const CLASS_MEMCACHED = 1;

      /**
       * Defines the class as Memcache
       *
       * @var int
       */
      const CLASS_MEMCACHE = 2;

      /**
       * The memcached instance
       *
       * @var Memcache|Memcached
       */
      protected $mc;

      /**
       * Whether the relevant cache extension is loaded
       *
       * @var boolean
       */
      protected static $loaded = null;

      /**
       * Instantiates the class
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param boolean $initialise_default_server Whether to add a server on construct
       */
      function __construct($initialise_default_server = true) {
         if (self::$loaded === null) {
            if (class_exists('\Memcached', false)) {
               self::$loaded = self::CLASS_MEMCACHED;
            } elseif (class_exists('\Memcache')) {
               self::$loaded = self::CLASS_MEMCACHE;
            } else {
               self::$loaded = false;
            }
         }

         if (self::$loaded !== null) {
            $this->mc = self::$loaded === self::CLASS_MEMCACHED ? new Memcached() : new Memcache();
            if ($initialise_default_server) {
               $this->addServer();
            }
         } else {
            trigger_error('Memcached extension not loaded - caching '
               . 'functions will not work', E_USER_NOTICE);
         }
         parent::__construct();

         \Log::debug(self::$loaded ? 'Loaded MemcachedWrapper' : 'MemcachedWrapper not loaded: extension unavailable');
      }

      /**
       * Returns the loaded cache class
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string|null
       */
      function getLoadedClass() {
         return self::$loaded ? get_class($this->mc) : null;
      }

      function purge() {
         return self::$loaded ? $this->mc->flush() : false;
      }

      function addServer($ip = ALO_MEMCACHED_IP, $port = ALO_MEMCACHED_PORT, $weight = 1) {
         \Log::debug('Added MemcachedWrapper server ' . $ip . ':' . $port
            . ' with a weight of ' . $weight);

         if (self::$loaded === self::CLASS_MEMCACHED) {
            return $this->mc->addServer($ip, $port, $weight);
         } elseif (self::$loaded === self::CLASS_MEMCACHE) {
            return $this->mc->addserver($ip, $port, null, $weight);
         } else {
            return false;
         }
      }

      function getStats() {
         return self::$loaded ? $this->mc->getStats() : false;
      }

      function delete($key) {
         return self::$loaded ? $this->mc->delete($key) : false;
      }

      function get($id) {
         return self::$loaded ? $this->mc->get($id) : false;
      }

      function set($key, $var, $expire = 3600) {
         \Log::debug('Set the MemcachedWrapper key ' . $key);

         if (self::$loaded === self::CLASS_MEMCACHED) {
            return $this->mc->set($key, $var, $expire);
         } elseif (self::$loaded === self::CLASS_MEMCACHE) {
            return $this->mc->set($key, $var, null, $expire);
         } else {
            return false;
         }
      }

      /**
       * The memcached version of getAll()
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return array
       */
      protected function getAllMemcached() {
         return $this->mc->getAllKeys();
      }

      /**
       * The Memcache version of getAll()
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return array
       */
      protected function getAllMemcache() {
         $dump = [];
         $slabs = $this->mc->getextendedstats('slabs');

         foreach ($slabs as $serverSlabs) {
            $keys = array_keys($serverSlabs);

            foreach ($keys as $k) {
               if (is_numeric($k)) {
                  try {
                     $d = $this->mc->getextendedstats('cachedump', (int)$k, 1000);

                     foreach ($d as $data) {
                        foreach ($data as $mc_key => $row) {
                           $dump[$mc_key] = $row[0];
                        }
                     }
                  } catch (\Exception $e) {
                     continue;
                  }
               }
            }
         }

         return $dump;
      }

      function getAll() {
         if (self::$loaded === self::CLASS_MEMCACHED) {
            return $this->getAllMemcached();
         } elseif (self::$loaded === self::CLASS_MEMCACHE) {
            return $this->getAllMemcache();
         } else {
            return [];
         }
      }

   }