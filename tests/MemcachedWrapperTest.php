<?php

   namespace Alo\Cache;

   class MemcachedWrapperTest extends \PHPUnit_Framework_TestCase {

      /**
       * @dataProvider definedProvider
       */
      function testDefined($const) {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $this->assertTrue(defined($const), $const . ' wasn\'t defined.');
      }

      function definedProvider() {
         return [
            ['ALO_MEMCACHED_IP'],
            ['ALO_MEMCACHED_PORT']
         ];
      }

      /**
       * @dataProvider provideTestValueSet
       */
      function testValueSet($key, $val) {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $mc = self::mc();

         $mc->set($key, $val);
         $get = $mc->get($key);

         $this->assertEquals($val,
                             $get,
                             _unit_dump([
                                           'Key'      => $key,
                                           'Val'      => $val,
                                           'Expected' => $val,
                                           'Actual'   => $get
                                        ]));
      }

      protected static function mc() {
         phpunit_debug('[MemcachedWrapperTest] mc()');
         if(!\Alo::$cache || !(\Alo::$cache instanceof MemcachedWrapper)) {
            \Alo::$cache = new MemcachedWrapper();
         }

         return \Alo::$cache;
      }

      function testPurge() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $mc = self::mc();

         $mc->set('foo', 1);

         $this->assertTrue($mc->purge(), 'Purge returned false');
      }

      function testDelete() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $mc = self::mc();

         $mc->set('test_del', 1);
         $mc->delete('test_del');

         $this->assertEmpty($mc->get('test_del'), 'Test_del returned: ' . $mc->get('test_del'));
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
