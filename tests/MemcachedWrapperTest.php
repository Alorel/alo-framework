<?php

   namespace Alo\Cache;

   class MemcachedWrapperTest extends \PHPUnit_Framework_TestCase {

      /**
       * @dataProvider provideTestValueSet
       */
      function testValueSet($key, $val) {
         $mc = new MemcachedWrapper();

         $mc->set($key, $val);
         $this->assertEquals($val, $mc->get($key));
      }

      function testPurge() {
         $mc = new MemcachedWrapper();

         $mc->set('foo', 1);
         $mc->purge();

         $this->assertEmpty($mc->getAll());
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
 