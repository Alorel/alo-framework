<?php
   /**
    * Created by PhpStorm.
    * User: Art
    * Date: 30/05/2015
    * Time: 14:39
    */

   namespace Alo\Cache;

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
         if(!\Alo::$cache || !(\Alo::$cache instanceof RedisWrapper)) {
            \Alo::$cache = new RedisWrapper();
         }

         return \Alo::$cache;
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

      function testGetAll() {
         $mc = self::mc();

         $mc->purge();
         $mc->set('aloframework', 'just works');
         $getall = $mc->getAll();

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
