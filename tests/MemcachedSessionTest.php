<?php

   namespace Alo\Session;

   use Alo\Cache\MemcachedWrapper;
   use PHPUNIT_GLOBAL;

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

         PHPUNIT_GLOBAL::$mcSession->foo = 'bar';
         PHPUNIT_GLOBAL::$mcSession->forceWrite();

         $id          = PHPUNIT_GLOBAL::$mcSession->getID();
         $sessFetched = \Alo::$cache->get(ALO_SESSION_MC_PREFIX . $id);

         $this->assertNotEmpty($sessFetched,
                               _unit_dump([
                                             'id'           => $id,
                                             'fetched'      => $sessFetched,
                                             'all'          => \Alo::$cache->getAll(),
                                             'is_available' => MemcachedWrapper::isAvailable()
                                          ]));

         $this->assertArrayHasKey('foo', $sessFetched, _unit_dump($sessFetched));
         $this->assertEquals('bar', $sessFetched['foo']);
      }

      function testToken() {
         $this->assertEquals(PHPUNIT_GLOBAL::$mcSession->getTokenExpected(), PHPUNIT_GLOBAL::$mcSession->getTokenActual());
      }

      function testRefreshToken() {
         $this->assertTrue(PHPUNIT_GLOBAL::$mcSession->refreshToken());
      }
   }
