<?php

    namespace Alo\Session;

    use Alo;
    use Alo\Cache\RedisWrapper;
    use Alo\Exception\LibraryException as Libex;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * Memcached-based session handler
         *
         * @author Art <a.molcanovas@gmail.com>
         */
        class RedisSession extends AbstractCacheSession {

            /**
             * Constructor
             *
             * @author Art <a.molcanovas@gmail.com>
             * @throws Libex When $instance is not passed and Alo::$cache does not contain a RedisWrapper instance
             *
             * @param RedisWrapper $instance If a parameter is passed here its instance will be used instead of Alo::$cache
             */
            function __construct(RedisWrapper &$instance = null) {
                if ($instance) {
                    $this->client = &$instance;
                } elseif (Alo::$cache && Alo::$cache instanceof RedisWrapper) {
                    $this->client = &Alo::$cache;
                } else {
                    throw new Libex('RedisWrapper instance not found', Libex::E_REQUIRED_LIB_NOT_FOUND);
                }

                $this->prefix = ALO_SESSION_REDIS_PREFIX;

                parent::__construct();
            }

            /**
             * Initialises a MySQLSession
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param RedisWrapper $dependcyObject If you don't want to use Alo::$db you can pass a RedisWrapper instance reference here.
             */
            static function init(RedisWrapper &$dependcyObject = null) {
                parent::initSession($dependcyObject, get_class());
            }
        }
    }
