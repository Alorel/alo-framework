<?php

   namespace Alo\Session;

   use Alo\Statics\Cookie;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }
   \Alo::loadConfig('session');

   /**
    * The session interface
    *
    * @author  Art <a.molcanovas@gmail.com>
    * @package Session
    */
   abstract class AbstractSession {

      /**
       * Hash algorithm in use
       *
       * @var string
       */
      const HASH_ALGO = 'sha512';

      /**
       * Session key under which key expiration data is stored
       *
       * @var string
       */
      const EXPIRE_KEY = '__expire';

      /**
       * The data array
       *
       * @var array
       */
      protected $data;

      /**
       * Whether to save session data
       *
       * @var boolean
       */
      protected $save;

      /**
       * Value of time()
       *
       * @var int
       */
      protected $time;

      /**
       * The session ID
       *
       * @var string
       */
      protected $id;

      /**
       * Instantiates the class
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      function __construct() {
         $this->data = [];
         $this->time = time();
         $this->save = true;

         if ($this->setID()->identityCheck()) {
            $this->fetch()->removeExpired();
         }
      }

      /**
       * Saves session data
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return AbstractSession
       */
      abstract protected function write();

      /**
       * Sets the session ID variable & the cookie
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return SQLSession
       */
      protected function setID() {
         $c = \get($_COOKIE[ALO_SESSION_COOKIE]);

         if ($c && strlen($c) == 128) {
            $this->id = $c;
         } else {
            $this->id = getUniqid(self::HASH_ALGO, 'session', 20);
         }

         \Log::debug('Session ID set to ' . $this->id);
         Cookie::set(ALO_SESSION_COOKIE, $this->id, $this->time + ALO_SESSION_TIMEOUT, '/', '', false, true);

         return $this;
      }

      /**
       * Fetches session data
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return AbstractSession
       */
      abstract protected function fetch();

      /**
       * Removes expired session keys
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return SQLSession
       */
      protected function removeExpired() {
         if (isset($this->data[self::EXPIRE_KEY])) {
            foreach ($this->data[self::EXPIRE_KEY] as $k => $v) {
               if ($this->time > $v) {
                  unset($this->data[self::EXPIRE_KEY][$k], $this->data[$k]);
               }
            }
            if (empty($this->data[self::EXPIRE_KEY])) {
               unset($this->data[self::EXPIRE_KEY]);
            }

            \Log::debug('Removed expired session keys');
         }

         return $this;
      }

      /**
       * Checks if the session hasn't been hijacked
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return boolean TRUE if the check has passed, FALSE if not and the
       *         session has been terminated.
       */
      protected function identityCheck() {
         $token = md5('sЕss' . \getFingerprint() . 'ия');

         if (!\get($this->data[ALO_SESSION_FINGERPRINT])) {
            $this->data[ALO_SESSION_FINGERPRINT] = $token;
            \Log::debug('Session identity check passed');
         } elseif ($token !== $this->data[ALO_SESSION_FINGERPRINT]) {
            \Log::debug('Session identity check failed');
            $this->terminate();

            return false;
         }

         return true;
      }

      /**
       * Gets a session value
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $key The identifier
       * @return mixed
       */
      function __get($key) {
         return \get($this->data[$key]);
      }

      /**
       * Sets a session value
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $key The identifier
       * @param mixed  $val The value
       */
      function __set($key, $val) {
         $this->data[$key] = $val;
      }

      /**
       * Returns a string representation of the session data
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      function __toString() {
         $r = 'ID: ' . $this->id . "\nData:";

         foreach ($this->data as $k => $v) {
            echo "\n\t$k => $v";
         }

         return $r;
      }

      /**
       * Deletes a session value
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string|array $key The corresponding key or array of keys
       * @return AbstractSession
       */
      function delete($key) {
         if (is_array($key)) {
            foreach ($key as $k) {
               unset($this->data[$k]);
            }
         } else {
            \Log::debug('Removed session key ' . $key);
            unset($this->data[$key]);
         }

         return $this;
      }

      /**
       * Returns all session data in an associative array
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return array
       */
      function getAll() {
         return $this->data;
      }

      /**
       * Returns the session ID
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      function getID() {
         return $this->id;
      }

      /**
       * Sets a session key to expire
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $key  The key
       * @param int    $time Expiration time in seconds
       * @return AbstractSession
       */
      function expire($key, $time) {
         $e = &$this->data[self::EXPIRE_KEY][$key];
         $e = $this->time + $time;

         \Log::debug('Set the session key ' . $key . ' to expire in ' . $time . ' seconds');

         return $this;
      }

      /**
       * Terminates the session
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return AbstractSession
       */
      function terminate() {
         $this->save = false;
         Cookie::delete(ALO_SESSION_COOKIE);
         \Log::debug('Terminated session');

         return $this;
      }

      /**
       * Saves session data if $this->save hasn't been changed to false
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      function __destruct() {
         if ($this->save) {
            $this->write();
         }
      }

   }