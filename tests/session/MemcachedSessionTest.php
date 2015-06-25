<?php

    ob_start();

    class MemcachedSessionTest extends AbstractSessionTest {

        function __construct($name = null, $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->wrapper = &PhuGlobal::$mcWrapper;
            $this->handler = 'Alo\Session\MemcachedSession';
            $this->prefix  = defined('ALO_SESSION_MC_PREFIX') ? ALO_SESSION_MC_PREFIX : '';
        }

        function definedProvider() {
            return [['ALO_SESSION_CLEANUP'],
                    ['ALO_SESSION_TIMEOUT'],
                    ['ALO_SESSION_COOKIE'],
                    ['ALO_SESSION_FINGERPRINT'],
                    ['ALO_SESSION_MC_PREFIX'],
                    ['ALO_SESSION_TABLE_NAME'],
                    ['ALO_SESSION_SECURE']];
        }
    }
