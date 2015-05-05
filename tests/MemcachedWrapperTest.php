<?php

   namespace Alo\Cache;

   use \Alo;

   class MemcachedWrapperTest extends \PHPUnit_Framework_TestCase {

      protected static function mc() {
         if (!Alo::$cache) {
            Alo::$cache = new MemcachedWrapper();
         }

         return Alo::$cache;
      }

      /**
       * @dataProvider provideTestValueSet
       */
      function testValueSet($key, $val) {
         $mc = self::mc();

         $mc->set($key, $val);
         $get = $mc->get($key);

         $this->assertEquals($val, $get, _unit_dump([
            'Key'      => $key,
            'Val'      => $val,
            'Expected' => $val,
            'Actual'   => $get
         ]));
      }

      function testPurge() {
         $mc = self::mc();

         $mc->set('foo', 1);

         $this->assertTrue($mc->purge(), 'Purge returned false');
      }

      function testDelete() {
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
 