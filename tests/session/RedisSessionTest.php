<?php

    ob_start();

    class RedisSessionTest extends AbstractSessionTest {

        function __construct($name = null, $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->wrapper = &PhuGlobal::$redisWrapper;
            $this->handler = 'Alo\Session\RedisSession';
            $this->prefix  = defined('ALO_SESSION_REDIS_PREFIX') ? ALO_SESSION_REDIS_PREFIX : '';
        }

        function definedProvider() {
            return [['ALO_SESSION_CLEANUP'],
                    ['ALO_SESSION_TIMEOUT'],
                    ['ALO_SESSION_COOKIE'],
                    ['ALO_SESSION_FINGERPRINT'],
                    ['ALO_SESSION_REDIS_PREFIX'],
                    ['ALO_SESSION_TABLE_NAME']];
        }
    }
