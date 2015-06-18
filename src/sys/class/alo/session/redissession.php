<?php

   namespace Alo\Session;

   use Alo;
   use Alo\Cache\RedisWrapper;
   use Alo\Exception\ExtensionException as EE;

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
          * @author Art <a.molcanovas@gmail.com>
          * @throws EE When a caching class is not available
          */
         function __construct() {
            if(Alo::$cache && (Alo::$cache instanceof RedisWrapper)) {
               $this->client = &Alo::$cache;
            } else {
               $this->client = new RedisWrapper(true);
            }

            if(!RedisWrapper::isAvailable()) {
               throw new EE('Redis extension not loaded.', EE::E_EXT_NOT_LOADED);
            } else {
               parent::__construct();
               $this->client = &Alo::$cache;
               $this->prefix = ALO_SESSION_REDIS_PREFIX;
               \Log::debug('Initialised Redis session');
            }
         }

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws EE When a caching class is not available
          *
          * @return RedisSession
          */
         static function redisSession() {
            return new RedisSession();
         }

      }
   }
