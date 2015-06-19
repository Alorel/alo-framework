<?php

   namespace Alo\Session;

   use Alo\Db\MySQL;
   use PhuGlobal;

   class MySQLSessionTest extends \PHPUnit_Framework_TestCase {

      function __construct($name = null, $data = [], $dataName = '') {
         parent::__construct($name, $data, $dataName);
         MySQLSession::destroySafely();
         MySQLSession::init(PhuGlobal::$mysql);
      }

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
         $_SESSION['foo'] = 'bar';
         $id              = session_id();
         session_write_close();

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
                                             'all'     => PhuGlobal::$mysql->prepQuery('SELECT * FROM `alo_session`')
                                          ]));

         $sessFetched = $sessFetched[0]['data'];

         $this->assertTrue(stripos($sessFetched, 'foo') !== false, '"foo" not found in session data');
         $this->assertTrue(stripos($sessFetched, 'bar') !== false, '"bar" not found in session data');
         ob_flush();
      }
   }
