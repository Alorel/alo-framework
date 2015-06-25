<?php

    namespace Alo\Cache;

    use PhuGlobal;

    class MemcachedWrapperTest extends \AbstractCacheTest {

        function __construct($name = null, $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->wrapper      = &PhuGlobal::$mcWrapper;
            $this->wrapper_name = 'MemcachedWrapper';
        }

        function definedProvider() {
            return [['ALO_MEMCACHED_IP'],
                    ['ALO_MEMCACHED_PORT']];
        }
    }
