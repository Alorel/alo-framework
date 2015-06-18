<?php

   namespace Alo\Session;

   use Alo\Cache\RedisWrapper;
   use PHPUNIT_GLOBAL;

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
         PHPUNIT_GLOBAL::$redisSession->foo = 'bar';
         PHPUNIT_GLOBAL::$redisSession->forceWrite();

         $id          = PHPUNIT_GLOBAL::$redisSession->getID();
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
         $this->assertEquals(PHPUNIT_GLOBAL::$redisSession->getTokenExpected(), PHPUNIT_GLOBAL::$redisSession->getTokenActual());
      }

      function testRefreshToken() {
         $this->assertTrue(PHPUNIT_GLOBAL::$redisSession->refreshToken());
      }
   }
