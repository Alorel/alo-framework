<?php

   namespace Alo\Cache;

   use PhuGlobal;

   class MemcachedWrapperTest extends \PHPUnit_Framework_TestCase {

      /**
       * @dataProvider definedProvider
       */
      function testDefined($const) {
         $this->assertTrue(defined($const), $const . ' wasn\'t defined.');
         ob_flush();
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
         PhuGlobal::$mcWrapper->set($key, $val);
         $get = PhuGlobal::$mcWrapper->get($key);

         $this->assertEquals($val,
                             $get,
                             _unit_dump([
                                           'Key'      => $key,
                                           'Val'      => $val,
                                           'Expected' => $val,
                                           'Actual'   => $get
                                        ]));
         ob_flush();
      }

      function testPurge() {
         PhuGlobal::$mcWrapper->set('foo', 1);

         $this->assertTrue(PhuGlobal::$mcWrapper->purge(), 'Purge returned false');
         ob_flush();
      }

      function testDelete() {
         PhuGlobal::$mcWrapper->set('test_del', 1);
         PhuGlobal::$mcWrapper->delete('test_del');

         $this->assertEmpty(PhuGlobal::$mcWrapper->get('test_del'), 'Test_del returned: ' . PhuGlobal::$mcWrapper->get('test_del'));
         ob_flush();
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
