<?php

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }
   /**
    * Probability of a session cleanup. To be called on request. Entering 100
    * would mean that there is a 1/100 chance.
    *
    * @var int
    */
   define('ALO_SESSION_CLEANUP', 150);

   /**
    * Session cookie timeout in seconds
    *
    * @var int
    */
   define('ALO_SESSION_TIMEOUT', 300);

   /**
    * The cookie name for sessions
    *
    * @var string
    */
   define('ALO_SESSION_COOKIE', 's');

   /**
    * The fingerprint variable key for sessions
    *
    * @var string
    */
   define('ALO_SESSION_FINGERPRINT', '_');

   /**
    * The prefix for MemcachedSession keys
    *
    * @var string
    */
   define('ALO_SESSION_MC_PREFIX', 'sess_');

   /**
    * The table to use for alo_session
    *
    * @var string
    */
   define('ALO_SESSION_TABLE_NAME', 'alo_session');