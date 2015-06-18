<?php

   namespace Alo\Session;

   use Alo\Db\MySQL;
   use PhuGlobal;

   class MySQLSessionTest extends \PHPUnit_Framework_TestCase {

      /**
       * @dataProvider definedProvider
       */
      function testDefined($key) {
         $this->assertTrue(defined($key), $key . ' wasn\'t defined');
         ob_flush();
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
         PhuGlobal::$mysqlsession->foo = 'bar';
         PhuGlobal::$mysqlsession->forceWrite();

         $id          = PhuGlobal::$mysqlsession->getID();
         $sql         = 'SELECT `data` FROM `alo_session` WHERE `id`=?';
         $sqlParams   = [$id];
         $sessFetched = PhuGlobal::$mysql->prepQuery($sql,
                                                     $sqlParams,
                                                     [
                                                        mySQL::V_CACHE => false
                                                     ]);

         $this->assertNotEmpty($sessFetched,
                               _unit_dump([
                                             'sql'     => $sql,
                                             'params'  => $sqlParams,
                                             'fetched' => $sessFetched,
                                             'all' => PhuGlobal::$mysql->prepQuery('SELECT * FROM `alo_session`')
                                          ]));

         $sessFetched = json_decode($sessFetched[0]['data'], true);

         $this->assertArrayHasKey('foo', $sessFetched, _unit_dump($sessFetched));
         $this->assertEquals('bar', $sessFetched['foo']);
         ob_flush();
      }

      function testToken() {
         $this->assertEquals(PhuGlobal::$mysqlsession->getTokenExpected(), PhuGlobal::$mysqlsession->getTokenActual());
         ob_flush();
      }

      function testRefreshToken() {
         $this->assertTrue(PhuGlobal::$mysqlsession->refreshToken());
         ob_flush();
      }
   }
