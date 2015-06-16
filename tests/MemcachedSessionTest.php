<?php

   namespace Alo\Session;

   use Alo\Cache\MemcachedWrapper;

   class MemcachedSessionTest extends \PHPUnit_Framework_TestCase {

      /**
       * @dataProvider definedProvider
       */
      function testDefined($key) {
         $this->assertTrue(defined($key), $key . ' wasn\'t defined');
      }

      function definedProvider() {
         return [
            ['ALO_SESSION_CLEANUP'],
            ['ALO_SESSION_TIMEOUT'],
            ['ALO_SESSION_COOKIE'],
            ['ALO_SESSION_FINGERPRINT'],
            ['ALO_SESSION_MC_PREFIX'],
            ['ALO_SESSION_TABLE_NAME'],
            ['ALO_SESSION_SECURE']
         ];
      }

      function testSave() {
         $s = self::sess();

         $s->foo      = 'bar';
         $force_write = $s->forceWrite();

         $id           = $s->getID();
         $sess_fetched = \Alo::$cache->get(ALO_SESSION_MC_PREFIX . $id);

         $this->assertNotEmpty($sess_fetched,
                               _unit_dump([
                                             'id'           => $id,
                                             'fetched'      => $sess_fetched,
                                             'all'          => \Alo::$cache->getAll(),
                                             'is_available' => MemcachedWrapper::is_available()
                                          ]));

         $this->assertArrayHasKey('foo', $sess_fetched, _unit_dump($sess_fetched));
         $this->assertEquals('bar', $sess_fetched['foo']);
      }

      static function sess() {
         if(!\Alo::$cache || !(\Alo::$cache instanceof MemcachedWrapper)) {
            \Alo::$cache = new MemcachedWrapper();
         }

         if(!\Alo::$session || !(\Alo::$session instanceof MemcachedSession)) {
            \Alo::$session = new MemcachedSession();
         }

         return \Alo::$session;
      }

      function testToken() {
         $s = self::sess();

         $this->assertEquals($s->getTokenExpected(), $s->getTokenActual());
      }

      function testRefreshToken() {
         $s = self::sess();

         $this->assertTrue($s->refreshToken());
      }
   }
