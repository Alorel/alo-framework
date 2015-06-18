<?php

   namespace Alo\Session;

   use Alo;
   use Alo\Cache\MemcachedWrapper;
   use Alo\Exception\ExtensionException as EE;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * The Memcached-based session handler. ALO_SESSION_CLEANUP is not used here as
       * cleanup is handled by the MySQL event handler
       *
       * @author  Art <a.molcanovas@gmail.com>
       * @package Session
       */
      class MemcachedSession extends AbstractCacheSession {

         /**
          * Instantiates the class
          *
          * @param MemcachedWrapper $cacheInstance If provided, will use this cache instance instead of Alo::$cache
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws EE When a caching class is not available
          */
         function __construct(MemcachedWrapper &$cacheInstance = null) {
            if($cacheInstance) {
               $this->client = &$cacheInstance;
            } elseif(Alo::$cache && Alo::$cache instanceof MemcachedWrapper) {
               $this->client = &Alo::$cache;
            } else {
               throw new EE('No caching PHP extension is loaded', EE::E_EXT_NOT_LOADED);
            }

            if(Alo::$cache && (Alo::$cache instanceof MemcachedWrapper)) {
               $this->client = &Alo::$cache;
            } else {
               $this->client = new MemcachedWrapper(true);
            }

            parent::__construct();
            $this->prefix = ALO_SESSION_MC_PREFIX;
            \Log::debug('Initialised Memcached session');
         }

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws EE When a caching class is not available
          *
          * @return MemcachedSession
          */
         static function memcachedSession() {
            return new MemcachedSession();
         }

      }
   }
