<?php

   namespace Alo\Session;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Legacy class name of the MySQLSession class
       *
       * @author     Art <a.molcanovas@gmail.com>
       * @deprecated Since 1.2
       */
      class SQLSession extends MySQLSession {

      }
   }