<?php

   namespace Alo\Session;

   use Alo\Cache\RedisWrapper;
   use PhuGlobal;

   class RedisSessionTest extends \PHPUnit_Framework_TestCase {

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
            ['ALO_SESSION_REDIS_PREFIX'],
            ['ALO_SESSION_TABLE_NAME']
         ];
      }

      function testSave() {
         PhuGlobal::$redisSession->foo = 'bar';
         PhuGlobal::$redisSession->forceWrite();

         $id = PhuGlobal::$redisSession->getID();
         $sessFetched = \Alo::$cache->get(ALO_SESSION_REDIS_PREFIX . $id);

         $this->assertNotEmpty($sessFetched,
                               _unit_dump([
                                             'id'           => $id,
                                             'fetched'      => $sessFetched,
                                             'all'          => \Alo::$cache->getAll(),
                                             'is_available' => RedisWrapper::isAvailable()
                                          ]));

         $this->assertArrayHasKey('foo', $sessFetched, _unit_dump($sessFetched));
         $this->assertEquals('bar', $sessFetched['foo']);
      }

      function testToken() {
         $this->assertEquals(PhuGlobal::$redisSession->getTokenExpected(), PhuGlobal::$redisSession->getTokenActual());
      }

      function testRefreshToken() {
         $this->assertTrue(PhuGlobal::$redisSession->refreshToken());
      }
   }
