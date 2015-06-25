<?php

    namespace Alo\Cache;

    use PhuGlobal;

    class RedisTest extends \AbstractCacheTest {

        function __construct($name = null, $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->wrapper      = &PhuGlobal::$redisWrapper;
            $this->wrapper_name = 'RedisWrapper';
        }

        function definedProvider() {
            return [['ALO_REDIS_IP'],
                    ['ALO_REDIS_PORT']];
        }

    }
