<?php

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /** The default locale to use */
        define('ALO_LOCALE_DEFAULT', 'en');

        /** The "page" which will identify the global locale */
        define('ALO_LOCALE_GLOBAL', 'global');

        /** Whether to fetch all the entries or just global and page-specific */
        define('ALO_LOCALE_FETCH_ALL', true);

        /** How long to keep locale strings cached (in seconds) */
        define('ALO_LOCALE_CACHE_TIME', 604800); //1 week
    }
