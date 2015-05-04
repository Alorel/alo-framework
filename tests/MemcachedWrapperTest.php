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
         $this->assertEquals($val, $mc->get($key));
      }

      function testPurge() {
         $mc = self::mc();

         $mc->set('foo', 1);

         $this->assertTrue($mc->purge());
      }

      function testDelete() {
         $mc = self::mc();

         $mc->set('test_del', 1);
         $mc->delete('test_del');

         $this->assertEmpty($mc->get('test_del'));
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
 