<?php

   namespace Alo\Session;

   use Alo\Cache\RedisWrapper;

   class RedisSessionTest extends \PHPUnit_Framework_TestCase {

      /**
       * @dataProvider definedProvider
       */
      function testDefined($key) {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $this->assertTrue(defined($key), $key . ' wasn\'t defined');
      }

      function definedProvider() {
         return [
            ['ALO_SESSION_CLEANUP'],
            ['ALO_SESSION_TIMEOUT'],
            ['ALO_SESSION_COOKIE'],
            ['ALO_SESSION_FINGERPRINT'],
            ['ALO_SESSION_REDIS_PREFIX'],
            ['ALO_SESSION_TABLE_NAME']
         ];
      }

      function testSave() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $s = self::sess();

         $s->foo      = 'bar';
         $force_write = $s->forceWrite();

         $id           = $s->getID();
         $sess_fetched = \Alo::$cache->get(ALO_SESSION_REDIS_PREFIX . $id);

         $this->assertNotEmpty($sess_fetched,
                               _unit_dump([
                                             'id'           => $id,
                                             'fetched'      => $sess_fetched,
                                             'all'          => \Alo::$cache->getAll(),
                                             'is_available' => RedisWrapper::is_available()
                                          ]));

         $this->assertArrayHasKey('foo', $sess_fetched, _unit_dump($sess_fetched));
         $this->assertEquals('bar', $sess_fetched['foo']);
      }

      static function sess() {
         phpunit_debug('[RedisSessionTest] sess()');
         if(!\Alo::$cache || !(\Alo::$cache instanceof RedisWrapper)) {
            \Alo::$cache = new RedisWrapper();
         }

         if(!\Alo::$session || !(\Alo::$session instanceof RedisSession)) {
            \Alo::$session = new RedisSession();
         }

         return \Alo::$session;
      }

      function testToken() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $s = self::sess();

         $this->assertEquals($s->getTokenExpected(), $s->getTokenActual());
      }

      function testRefreshToken() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $s = self::sess();

         $this->assertTrue($s->refreshToken());
      }
   }
