<?php

   namespace Alo\Session;

   use Alo;
   use Alo\Cache\MemcachedWrapper;
   use Alo\Exception\LibraryException as Libex;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Memcached-based session handler
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      class MemcachedSession extends AbstractCacheSession {

         /**
          * Constructor
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws Libex When $instance is not passed and Alo::$cache does not contain a MemcachedWrapper instance
          *
          * @param MemcachedWrapper $instance If a parameter is passed here its instance will be used instead of Alo::$cache
          */
         function __construct(MemcachedWrapper &$instance = null) {
            if($instance) {
               $this->client = &$instance;
            } elseif(Alo::$cache && Alo::$cache instanceof MemcachedWrapper) {
               $this->client = &Alo::$cache;
            } else {
               throw new Libex('MemcachedWrapper instance not found', Libex::E_REQUIRED_LIB_NOT_FOUND);
            }

            $this->prefix = ALO_SESSION_MC_PREFIX;

            parent::__construct();
         }
      }
   }
