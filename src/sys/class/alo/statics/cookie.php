<?php

   namespace Alo\Statics;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Cookie handler
       *
       * @author  Art <a.molcanovas@gmail.com>
       * @package Statics
       */
      abstract class Cookie {

         /**
          * Wrapper for PHP's setcookie with
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string      $name     Cookie name
          * @param string      $value    Cookie value
          * @param int|boolean $expire   False if deleting a cookie,
          *                              0 for session-length cookie, expiration time in seconds otherwise
          * @param string      $path     The path the cookie will be available at
          * @param string      $domain   The domain the cookie will be available at
          * @param boolean     $secure   Whether to only transfer the cookie via HTTPS.
          * @param boolean     $httponly Whether to only allow server-side usage of the cookie
          *
          * @return boolean Whether the cookie was set. Always returns false on CLI requests.
          */
         static function set($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = true) {
            if(!\Alo::$router->is_cli_request() && !defined('PHPUNIT_RUNNING')) {
               $expire   = (int)$expire;
               $secure   = (bool)$secure;
               $httponly = (bool)$httponly;

               if($expire === false) {
                  $expire = time() - 100;
               } elseif($expire > 0) {
                  $expire = time() + $expire;
               }

               \Log::debug('Set cookie ' . $name . ' to ' . $value . ' (expires ' . date('Y-m-d H:i:s', $expire) . ')');

               return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
            } else {
               \Log::debug('Not setting cookie ' . $name . ' as we\'re in CLI mode');

               return false;
            }
         }

         /**
          * Deletes a cookie
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $name Cookie name
          *
          * @return boolean Whether the cookie was deleted. Always returns false on CLI requests.
          */
         static function delete($name) {
            if(!\Alo::$router->is_cli_request() && !defined('PHPUNIT_RUNNING')) {
               \Log::debug('Deleted cookie ' . $name);

               return setcookie($name, '', false, '/', '', false, true);
            } else {
               \Log::debug('Not deleting cookie ' . $name . ' as we\'re in CLI mode');

               return false;
            }
         }

      }
   }