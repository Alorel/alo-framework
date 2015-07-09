<?php

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /** The default Memcached IP to use */
        define('ALO_MEMCACHED_IP', '127.0.0.1');

        /** The default Memcached port to use */
        define('ALO_MEMCACHED_PORT', 11211);
    }
