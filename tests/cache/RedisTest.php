<?php

    namespace Alo\Cache;

    use PhuGlobal;

    class RedisTest extends \PHPUnit_Framework_TestCase {

        /**
         * @dataProvider definedProvider
         */
        function testDefined($const) {
            $this->assertTrue(defined($const), $const . ' wasn\'t defined.');
        }

        function definedProvider() {
            return [['ALO_REDIS_IP'],
                    ['ALO_REDIS_PORT']];
        }

        /**
         * @dataProvider provideTestValueSet
         */
        function testValueSet($key, $val) {
            PhuGlobal::$redisWrapper->set($key, $val);
            $get = PhuGlobal::$redisWrapper->get($key);

            $this->assertEquals($val, $get, _unit_dump(['Key'      => $key,
                                                        'Val'      => $val,
                                                        'Expected' => $val,
                                                        'Actual'   => $get]));
        }

        function testPurge() {
            PhuGlobal::$redisWrapper->set('foo', 1);

            $this->assertTrue(PhuGlobal::$redisWrapper->purge(), 'Purge returned false');
        }

        function testDelete() {
            PhuGlobal::$redisWrapper->set('test_del', 1);
            PhuGlobal::$redisWrapper->delete('test_del');

            $this->assertEmpty(PhuGlobal::$redisWrapper->get('test_del'),
                               'Test_del returned: ' . PhuGlobal::$redisWrapper->get('test_del'));
        }

        function testGetAll() {
            PhuGlobal::$redisWrapper->purge();
            PhuGlobal::$redisWrapper->set('aloframework', 'just works');
            $getall = PhuGlobal::$redisWrapper->getAll();

            $this->assertEquals(['aloframework' => 'just works'], $getall, _unit_dump($getall));
        }

        function provideTestValueSet() {
            return [['val_string', 'str'],
                    ['val_int', 515],
                    ['val_float', 1.1],
                    ['val_array', ['foo' => 'bar']],
                    ['val_obj', new \stdClass]];
        }
    }
