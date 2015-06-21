<?php

    use Alo\Cache\MemcachedWrapper;
    use Alo\Cache\RedisWrapper;
    use Alo\Cron;
    use Alo\Db\MySQL;
    use Alo\Locale;

    ob_start();
    include_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'index.php';

    /** @var string Essentially the root dir */
    define('PHPUNIT_ROOT', __DIR__ . DIRECTORY_SEPARATOR);

    /** @var bool Let the framework know that PHPUnit is running */
    define('PHPUNIT_RUNNING', true);

    /**
     * Loads class files
     * @author Art <a.molcanovas@gmail.com>
     *
     * @param string $dirName Directory path
     */
    function _load_classes($dirName) {
        $di = new DirectoryIterator($dirName);
        foreach ($di as $file) {
            if ($file->isDir() && !$file->isLink() && !$file->isDot()) {
                // recurse into directories other than a few special ones
                _load_classes($file->getPathname());
            } elseif (substr($file->getFilename(), -4) === '.php') {
                // save the class name / path of a .php file found
                include_once $file->getPathname();
            }
        }
    }

    /**
     * Provides -useful- output on a failed test
     * @author Art <a.molcanovas@gmail.com>
     *
     * @param mixed $data Data to dump
     *
     * @return string
     */
    function _unit_dump($data) {
        ob_start();
        var_dump($data);

        return ob_get_clean();
    }

    // ========== Autoload classes ==========
    _load_classes(DIR_SYS . 'core');
    _load_classes(DIR_SYS . 'class');
    _load_classes(DIR_APP . 'class');
    _load_classes(DIR_APP . 'interface');
    _load_classes(DIR_APP . 'traits');

    Alo::$router = new \Alo\Controller\Router();
    ob_clean();

    includeonceifexists(__DIR__ . '/vendor/autoload.php');
    error_reporting(E_ALL);

    /** @var array $runThisSQL The SQL in this array will be executed once the PHPUnit MySQL instance is up */
    $runThisSQL = ['CREATE TABLE IF NOT EXISTS `alo_session` (`id`     CHAR(128)
           CHARACTER SET `ascii` NOT NULL,
  `data`   VARCHAR(16000)      NOT NULL,
  `access` TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `access` (`access`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =`utf8mb4`;',
                   'CREATE TABLE IF NOT EXISTS `alo_locale` (
	`id`    INT UNSIGNED                 NOT NULL AUTO_INCREMENT,
	`lang`  CHAR(2)
	        COLLATE `ascii_general_ci`   NOT NULL,
	`page`  VARCHAR(25)
	        COLLATE `ascii_general_ci`   NOT NULL,
	`key`   VARCHAR(25)
	        COLLATE `ascii_general_ci`   NOT NULL,
	`value` TEXT
	        COLLATE `utf8mb4_general_ci` NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `lang_page_key` (`lang`, `page`, `key`)
)
	ENGINE = InnoDB
	DEFAULT CHARSET = `utf8mb4`;'];

    /**
     * Global PHPUnit container class
     * @author Art <a.molcanovas@gmail.com>
     */
    abstract class PhuGlobal {

        /** @var Cron */
        static $cron;

        /** @var MemcachedWrapper */
        static $mcWrapper;

        /** @var RedisWrapper */
        static $redisWrapper;

        /** @var MySQL */
        static $mysql;

        /** @var Locale */
        static $locale;
    }

    if (!serverIsWindows()) {
        PhuGlobal::$cron = new Cron();
    }

    PhuGlobal::$mcWrapper    = new MemcachedWrapper();
    PhuGlobal::$redisWrapper = new RedisWrapper();
    PhuGlobal::$mysql        =
        new MySQL(ALO_MYSQL_SERVER, ALO_MYSQL_PORT, ALO_MYSQL_USER, ALO_MYSQL_PW, 'phpunit', ALO_MYSQL_CACHE);

    foreach ($runThisSQL as $sql) {
        PhuGlobal::$mysql->prepQuery($sql);
    }

    PhuGlobal::$locale = new Locale(PhuGlobal::$mysql);
