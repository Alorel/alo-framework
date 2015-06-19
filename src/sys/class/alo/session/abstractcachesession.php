<?php

   namespace Alo\Session;

   use Alo;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Abstraction for cache-based session handlers
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      abstract class AbstractCacheSession extends AbstractSession {

         /**
          * MemcachedWrapper instance
          *
          * @var Alo\Cache\MemcachedWrapper|Alo\Cache\RedisWrapper
          */
         protected $client;

         /**
          * Cache key prefix
          *
          * @var string
          */
         protected $prefix;

         /**
          * Destroys a session
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $sessionID The ID to destroy
          *
          * @return bool
          */
         public function destroy($sessionID) {
            parent::destroy($sessionID);

            return $this->client->delete($this->prefix . $sessionID);
         }

         /**
          * Read ssession data
          *
          * @author Art <a.molcanovas@gmail.com>
          * @link   http://php.net/manual/en/sessionhandlerinterface.read.php
          *
          * @param string $sessionID The session id to read data for.
          *
          * @return string
          */
         public function read($sessionID) {
            $data = $this->client->get($this->prefix . $sessionID);

            return $data ? $data : '';
         }

         /**
          * Write session data
          *
          * @author Art <a.molcanovas@gmail.com>
          * @link   http://php.net/manual/en/sessionhandlerinterface.write.php
          *
          * @param string $sessionID    The session id.
          * @param string $sessionData  The encoded session data. This data is the
          *                             result of the PHP internally encoding
          *                             the $_SESSION superglobal to a serialized
          *                             string and passing it as this parameter.
          *                             Please note sessions use an alternative serialization method.
          *
          * @return bool
          */
         public function write($sessionID, $sessionData) {
            return $this->client->set($this->prefix . $sessionID, $sessionData, ALO_SESSION_TIMEOUT);
         }
      }
   }
