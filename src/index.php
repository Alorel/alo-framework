<?php

   /**
    * To calculate generation time
    *
    * @var float
    */
   define('GEN_START', microtime(true));

   // ===== Miscellaneous constants' definitions BEGIN =====

   /**
    * Defines a logging level as "debug"
    *
    * @var int
    */
   define('LOG_LEVEL_DEBUG', 2);

   /**
    * Defines a logging level as "error"
    *
    * @var int
    */
   define('LOG_LEVEL_ERROR', 1);

   /**
    * Defines a logging level as "no logging"
    *
    * @var int
    */
   define('LOG_LEVEL_NONE', 0);

   /**
    * Defines the environment as "setup"
    *
    * @var int
    */
   define('ENV_SETUP', 0);

   /**
    * Defines the environment as "development"
    *
    * @var int
    */
   define('ENV_DEVELOPMENT', 1);

   /**
    * Defines the environment as "production"
    *
    * @var int
    */
   define('ENV_PRODUCTION', 2);

   // ===== Miscellaneous constants' definitions END =====

   // ===== General setup BEGIN =====

   /**
    * Defines the log level. Valid values are LOG_LEVEL_DEBUG, LOG_LEVEL_ERROR and LOG_LEVEL_NONE
    *
    * @var string
    */
   define('LOG_LEVEL', LOG_LEVEL_DEBUG);

   /**
    * If logging is set to intense, an entire debug backtrace will be appended to
    * each row, otherwise it's just the last file and line number
    *
    * @var bool
    */
   define('LOG_INTENSE', false);

   /**
    * The application environment. Valid values are "setup" "dev
    */
   define('ENVIRONMENT', ENV_SETUP);

   // ===== General setup END. You needn't change anything below this line =====

   /**
    * Index directory
    *
    * @var string
    */
   define('DIR_INDEX', __DIR__ . DIRECTORY_SEPARATOR);

   /**
    * Application directory
    *
    * @var string
    */
   define('DIR_APP', DIR_INDEX . 'app' . DIRECTORY_SEPARATOR);

   /**
    * The temp directory
    *
    * @var string
    */
   define('DIR_TMP', DIR_APP . 'tmp' . DIRECTORY_SEPARATOR);

   /**
    * System directory
    *
    * @var string
    */
   define('DIR_SYS', DIR_INDEX . 'sys' . DIRECTORY_SEPARATOR);

   /**
    * Controllers directory
    *
    * @var string
    */
   define('DIR_CONTROLLERS', DIR_APP . 'controllers' . DIRECTORY_SEPARATOR);

   /**
    * The minimum available PHP integer
    *
    * @var int
    */
   define('PHP_INT_MIN', ~PHP_INT_MAX);

   // Change dir to make sure CLI requests are correct
   if(defined('STDIN')) {
      chdir(dirname(__FILE__));
   }

   require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'core.php';
