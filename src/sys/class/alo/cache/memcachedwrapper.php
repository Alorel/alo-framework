<?php

   namespace Alo\Cache;

   use Memcache;
   use Memcached;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

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
          * Whether the relevant cache extension is loaded
          *
          * @var boolean
          */
         protected static $loaded = null;
         /**
          * The memcached instance
          *
          * @var Memcache|Memcached
          */
         protected $client;

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param boolean $initDefaultServer Whether to add a server on construct
          */
         function __construct($initDefaultServer = true) {
            if(self::$loaded === null) {
               if(class_exists('\Memcached', false)) {
                  self::$loaded = self::CLASS_MEMCACHED;
               } elseif(class_exists('\Memcache')) {
                  self::$loaded = self::CLASS_MEMCACHE;
               } else {
                  self::$loaded = false;
               }
            }

            if(self::$loaded !== null) {
               $this->client = self::$loaded === self::CLASS_MEMCACHED ? new Memcached() : new Memcache();
               if($initDefaultServer) {
                  $this->addServer();
               }
            } else {
               php_warning('Memcached extension not loaded - caching '
                           . 'functions will not work');
            }
            parent::__construct();

            \Log::debug(self::$loaded ? 'Loaded MemcachedWrapper' : 'MemcachedWrapper not loaded: extension unavailable');
         }

         /**
          * Adds a server to the pool
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $ip     The server IP
          * @param int    $port   The server port
          * @param int    $weight The server's weight, ie how likely it is to be used
          *
          * @return boolean
          */
         function addServer($ip = ALO_MEMCACHED_IP, $port = ALO_MEMCACHED_PORT, $weight = 1) {
            \Log::debug('Added MemcachedWrapper server ' . $ip . ':' . $port
                        . ' with a weight of ' . $weight);

            if(self::$loaded === self::CLASS_MEMCACHED) {
               return $this->client->addServer($ip, $port, $weight);
            } elseif(self::$loaded === self::CLASS_MEMCACHE) {
               return $this->client->addserver($ip, $port, null, $weight);
            } else {
               return false;
            }
         }

         /**
          * Deletes a memcache key
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $key The key
          *
          * @return boolean
          */
         function delete($key) {
            return self::$loaded ? $this->client->delete($key) : false;
         }

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param boolean $initialise_default_server Whether to add a server on construct
          *
          * @return MemcachedWrapper
          */
         static function MemcachedWrapper($initialise_default_server = true) {
            return new MemcachedWrapper($initialise_default_server);
         }

         /**
          * Returns the loaded cache class
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string|null
          */
         function getLoadedClass() {
            return self::$loaded ? get_class($this->client) : null;
         }

         /**
          * Gets cache process info
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return array
          */
         function getStats() {
            return self::$loaded ? $this->client->getStats() : false;
         }

         /**
          * Checks if a Memcache or Memcached is available
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return boolean
          */
         static function isAvailable() {
            return class_exists('\Memcached') || class_exists('\Memcache');
         }

         /**
          * Clears all items from cache
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return boolean
          */
         function purge() {
            return self::$loaded ? $this->client->flush() : false;
         }

         /**
          * Gets a cached value
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $id The value's key
          *
          * @return mixed
          */
         function get($id) {
            return self::$loaded ? $this->client->get($id) : false;
         }

         /**
          * Sets a cached key/value pair
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $key    The key identifier
          * @param mixed  $var    The value to set
          * @param int    $expire When to expire the set data. Defaults to 3600s.
          *
          * @return boolean
          */
         function set($key, $var, $expire = 3600) {
            \Log::debug('Set the MemcachedWrapper key ' . $key);

            if(self::$loaded === self::CLASS_MEMCACHED) {
               return $this->client->set($key, $var, $expire);
            } elseif(self::$loaded === self::CLASS_MEMCACHE) {
               return $this->client->set($key, $var, null, $expire);
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
            $keys = $this->client->getAllKeys();
            $vals = [];

            foreach($keys as $k) {
               $vals[$k] = $this->get($k);
            }

            return $vals;
         }

         /**
          * The Memcache version of getAll()
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return array
          */
         protected function getAllMemcache() {
            $dump     = [];
            $allSlabs = $this->client->getExtendedStats('slabs');
            ob_start();

            foreach($allSlabs as $server => $slabs) {
               foreach($slabs AS $slabId => $slabMeta) {
                  try {
                     if($cdump = $this->client->getExtendedStats('cachedump', (int)$slabId)) {
                        foreach($cdump AS $keys => $arrVal) {
                           if(is_array($arrVal)) {
                              foreach($arrVal AS $k => $v) {
                                 if($k != 'CLIENT_ERROR') {
                                    $dump[$k] = $this->get($k);
                                 }
                              }
                           }
                        }
                     }
                  } catch(\Exception $e) {
                     continue;
                  }
               }
            }

            ob_end_clean();

            return $dump;
         }

         /**
          * Return all cached keys and values
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return array
          */
         function getAll() {
            if(self::$loaded === self::CLASS_MEMCACHED) {
               return $this->getAllMemcached();
            } elseif(self::$loaded === self::CLASS_MEMCACHE) {
               return $this->getAllMemcache();
            } else {
               return [];
            }
         }

      }
   }
