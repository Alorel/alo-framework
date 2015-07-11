<?php

    /** Defines a logging level as "debug" */
    define('LOG_LEVEL_DEBUG', 3);

    /** Defines a logging level as "warning" */
    define('LOG_LEVEL_WARNING', 2);

    /** Defines a logging level as "error" */
    define('LOG_LEVEL_ERROR', 1);

    /** Defines a logging level as "no logging" */
    define('LOG_LEVEL_NONE', 0);

    /** Defines the environment as "setup" */
    define('ENV_SETUP', 0);

    /** Defines the environment as "development" */
    define('ENV_DEVELOPMENT', 1);

    /** Defines the environment as "production" */
    define('ENV_PRODUCTION', 2);

    /** Application directory */
    define('DIR_APP', DIR_INDEX . 'app' . DIRECTORY_SEPARATOR);

    /** The temp directory */
    define('DIR_TMP', DIR_APP . 'tmp' . DIRECTORY_SEPARATOR);

    /** System directory */
    define('DIR_SYS', DIR_INDEX . 'sys' . DIRECTORY_SEPARATOR);

    /** Controllers directory */
    define('DIR_CONTROLLERS', DIR_APP . 'controllers' . DIRECTORY_SEPARATOR);

    /** Configuration directory */
    define('DIR_CONFIG', DIR_APP . 'config' . DIRECTORY_SEPARATOR);

    /** Error directory */
    define('DIR_ERROR', DIR_APP . 'error' . DIRECTORY_SEPARATOR);

    /** Log directory */
    define('DIR_LOGS', DIR_APP . 'logs' . DIRECTORY_SEPARATOR);

    /** The minimum available PHP integer */
    define('PHP_INT_MIN', ~PHP_INT_MAX);
