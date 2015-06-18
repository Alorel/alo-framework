<?php

   namespace Alo\Session;

   use Alo\Db\MySQL;
   use PHPUNIT_GLOBAL;

   class MySQLSessionTest extends \PHPUnit_Framework_TestCase {

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
         PHPUNIT_GLOBAL::$mysqlsession->foo = 'bar';
         PHPUNIT_GLOBAL::$mysqlsession->forceWrite();

         $id          = PHPUNIT_GLOBAL::$mysqlsession->getID();
         $sql         = 'SELECT `data` FROM `alo_session` WHERE `id`=?';
         $sqlParams   = [$id];
         $sessFetched = PHPUNIT_GLOBAL::$mysql->prepQuery($sql,
                                                          $sqlParams,
                                                          [
                                                             mySQL::V_CACHE => false
                                                          ]);

         $this->assertNotEmpty($sessFetched,
                               _unit_dump([
                                             'sql'     => $sql,
                                             'params'  => $sqlParams,
                                             'fetched' => $sessFetched,
                                             'all'     => PHPUNIT_GLOBAL::$mysql->prepQuery('SELECT * FROM `alo_session`')
                                          ]));

         $sessFetched = json_decode($sessFetched[0]['data'], true);

         $this->assertArrayHasKey('foo', $sessFetched, _unit_dump($sessFetched));
         $this->assertEquals('bar', $sessFetched['foo']);
      }

      function testToken() {
         $this->assertEquals(PHPUNIT_GLOBAL::$mysqlsession->getTokenExpected(), PHPUNIT_GLOBAL::$mysqlsession->getTokenActual());
      }

      function testRefreshToken() {
         $this->assertTrue(PHPUNIT_GLOBAL::$mysqlsession->refreshToken());
      }
   }
