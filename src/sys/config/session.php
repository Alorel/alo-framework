<?php

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /** The session handler to use */
        define('ALO_SESSION_HANDLER', '\Alo\Session\MySQLSession');

        /**
         * Probability of a session cleanup. To be called on request. Entering 100
         * would mean that there is a 1/100 chance.
         */
        define('ALO_SESSION_CLEANUP', 150);

        /** Session cookie timeout in seconds */
        define('ALO_SESSION_TIMEOUT', 300);

        /** The cookie name for sessions */
        define('ALO_SESSION_COOKIE', 's');

        /** The fingerprint variable key for sessions */
        define('ALO_SESSION_FINGERPRINT', '_');

        /** The prefix for MemcachedSession keys */
        define('ALO_SESSION_MC_PREFIX', 's_mc_');

        /** The prefix for RedisSession keys */
        define('ALO_SESSION_REDIS_PREFIX', 's_red_');

        /** The table to use for MySQL sessions */
        define('ALO_SESSION_TABLE_NAME', 'alo_session');

        /** Whether to only transmit the session cookie on a HTTPS connection */
        define('ALO_SESSION_SECURE', false);
    }
