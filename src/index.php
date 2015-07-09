<?php
    //v2.1.1

    /** Microtime float when the script started */
    define('GEN_START', microtime(true));

    /** root/index.php directory */
    define('DIR_INDEX', __DIR__ . DIRECTORY_SEPARATOR);

    require_once 'sys' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'global_defines.php';

    // ===== General setup BEGIN =====

    /** The application environment. Valid values are "setup" "dev" */
    define('ENVIRONMENT', ENV_SETUP);

    /** Defines the log level. Valid values are LOG_LEVEL_NONE LOG_LEVEL_DEBUG, LOG_LEVEL_WARNING and LOG_LEVEL_ERROR */
    define('LOG_LEVEL', LOG_LEVEL_WARNING);

    /**
     * If logging is set to intense, an entire debug backtrace will be appended to each row, otherwise it's just the
     * last file and line number
     */
    define('LOG_INTENSE', false);

    // ===== General setup END. You needn't change anything below this line =====

    // Change dir to make sure CLI requests are correct
    if (defined('STDIN')) {
        chdir(dirname(__FILE__));
    }

    require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'core.php';
