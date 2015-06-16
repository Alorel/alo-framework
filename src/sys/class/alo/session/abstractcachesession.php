<?php

   namespace Alo\Session;

   use Alo\Cache\AbstractCache;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      \Alo::loadConfig('session');

      /**
       * The session interface
       *
       * @author  Art <a.molcanovas@gmail.com>
       * @package Session
       */
      abstract class AbstractCacheSession extends AbstractSession {

         /**
          * Reference to cache wrapper instance
          *
          * @var AbstractCache
          */
         protected $client;

         /**
          * Cache prefix to use
          *
          * @var string
          */
         protected $prefix;

         /**
          * Fetches session data
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return MemcachedSession
          */
         protected function fetch() {
            $data = $this->client->get($this->prefix . $this->id);

            if($data) {
               $this->data = $data;
            }

            \Log::debug('Fetched session data');

            return $this;
         }

         /**
          * Terminates the session
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return MemcachedSession
          */
         function terminate() {
            $this->client->delete($this->prefix . $this->id);

            return parent::terminate();
         }

         /**
          * Saves session data
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return MemcachedSession
          */
         protected function write() {
            $this->client->set($this->prefix . $this->id, $this->data, ALO_SESSION_TIMEOUT);
            \Log::debug('Saved session data');

            return $this;
         }
      }
   }
