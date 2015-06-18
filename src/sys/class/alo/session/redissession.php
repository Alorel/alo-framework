<?php

   namespace Alo\Session;

   use Alo;
   use Alo\Cache\RedisWrapper;
   use Alo\Exception\LibraryException as Libex;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * The Redis-based session handler. ALO_SESSION_CLEANUP is not used here as
       * cleanup is handled by the MySQL event handler
       *
       * @author  Art <a.molcanovas@gmail.com>
       * @package Session
       */
      class RedisSession extends AbstractCacheSession {

         /**
          * Instantiates the class
          *
          * @param RedisWrapper $cacheInstance If provided, will use this cache instance instead of Alo::$cache
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws Libex When $cacheInstance is not passed and Alo::$cache does not contain a MemcachedWrapper instance
          */
         function __construct(RedisWrapper &$cacheInstance = null) {
            if($cacheInstance) {
               $this->client = &$cacheInstance;
            } elseif(Alo::$cache && Alo::$cache instanceof RedisWrapper) {
               $this->client = &Alo::$cache;
            } else {
               throw new Libex('RedisWrapper instance not found.', Libex::E_REQUIRED_LIB_NOT_FOUND);
            }

            if(Alo::$cache && (Alo::$cache instanceof RedisWrapper)) {
               $this->client = &Alo::$cache;
            } else {
               $this->client = new RedisWrapper(true);
            }

            parent::__construct();
            $this->prefix = ALO_SESSION_REDIS_PREFIX;
            \Log::debug('Initialised Redis session');
         }

         /**
          * Instantiates the class
          *
          * @param RedisWrapper $cacheInstance If provided, will use this cache instance instead of Alo::$cache
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws Libex When $cacheInstance is not passed and Alo::$cache does not contain a MemcachedWrapper instance
          *
          * @return RedisSession
          */
         static function redisSession(RedisWrapper &$cacheInstance = null) {
            return new RedisSession($cacheInstance);
         }

      }
   }
