<?php

   namespace Alo\Cache;

   use PHPUNIT_GLOBAL;

   class RedisTest extends \PHPUnit_Framework_TestCase {
      /**
       * @dataProvider definedProvider
       */
      function testDefined($const) {
         $this->assertTrue(defined($const), $const . ' wasn\'t defined.');
      }

      function definedProvider() {
         return [
            ['ALO_REDIS_IP'],
            ['ALO_REDIS_PORT']
         ];
      }

      /**
       * @dataProvider provideTestValueSet
       */
      function testValueSet($key, $val) {
         PHPUNIT_GLOBAL::$redisWrapper->set($key, $val);
         $get = PHPUNIT_GLOBAL::$redisWrapper->get($key);

         $this->assertEquals($val,
                             $get,
                             _unit_dump([
                                           'Key'      => $key,
                                           'Val'      => $val,
                                           'Expected' => $val,
                                           'Actual'   => $get
                                        ]));
      }

      function testPurge() {
         PHPUNIT_GLOBAL::$redisWrapper->set('foo', 1);

         $this->assertTrue(PHPUNIT_GLOBAL::$redisWrapper->purge(), 'Purge returned false');
      }

      function testDelete() {
         PHPUNIT_GLOBAL::$redisWrapper->set('test_del', 1);
         PHPUNIT_GLOBAL::$redisWrapper->delete('test_del');

         $this->assertEmpty(PHPUNIT_GLOBAL::$redisWrapper->get('test_del'), 'Test_del returned: ' . PHPUNIT_GLOBAL::$redisWrapper->get('test_del'));
      }

      function testGetAll() {
         PHPUNIT_GLOBAL::$redisWrapper->purge();
         PHPUNIT_GLOBAL::$redisWrapper->set('aloframework', 'just works');
         $getall = PHPUNIT_GLOBAL::$redisWrapper->getAll();

         $this->assertEquals(['aloframework' => 'just works'], $getall, _unit_dump($getall));
      }

      function provideTestValueSet() {
         return [
            ['val_string', 'str'],
            ['val_int', 515],
            ['val_float', 1.1],
            ['val_array', ['foo' => 'bar']],
            ['val_obj', new \stdClass]
         ];
      }
   }
