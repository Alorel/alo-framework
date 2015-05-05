<?php

   namespace Alo\Session;

   use Alo;
   use Alo\Cache\AbstractCache;
   use Alo\Cache\MemcachedWrapper;
   use Alo\Exception\ExtensionException as EE;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * The Memcached-based session handler. ALO_SESSION_CLEANUP is not used here as
    * cleanup is handled by the MySQL event handler
    *
    * @author  Art <a.molcanovas@gmail.com>
    * @package Session
    */
   class MemcachedSession extends AbstractSession {

      /**
       * Reference to database instance
       *
       * @var AbstractCache
       */
      protected $mc;

      /**
       * Instantiates the class
       *
       * @author Art <a.molcanovas@gmail.com>
       * @throws EE When a caching class is not available
       */
      function __construct() {
         if (!Alo::$cache) {
            Alo::$cache = new MemcachedWrapper(true);
         }

         if (!AbstractCache::is_available()) {
            throw new EE('No caching PHP extension is loaded', EE::E_EXT_NOT_LOADED);
         }

         $this->mc = &Alo::$cache;
         parent::__construct();
         \Log::debug('Initialised Memcached session');
      }

      protected function write() {
         $this->mc->set(ALO_SESSION_MC_PREFIX . $this->id, json_encode($this->data), ALO_SESSION_TIMEOUT);
         \Log::debug('Saved session data');

         return $this;
      }

      protected function fetch() {
         $data = $this->mc->get(ALO_SESSION_MC_PREFIX . $this->id);

         if ($data) {
            $this->data = json_decode($data, true);
         }

         \Log::debug('Fetched session data');

         return $this;
      }

      function terminate() {
         $this->mc->delete(ALO_SESSION_MC_PREFIX . $this->id);

         return parent::terminate();
      }

   }