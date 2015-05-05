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
            ['ALO_SESSION_TABLE_NAME']
         ];
      }

      static function sess() {
         if (!\Alo::$cache) {
            \Alo::$cache = new MemcachedWrapper();
         }

         if (!\Alo::$session) {
            \Alo::$session = new MemcachedSession();
         }

         return \Alo::$session;
      }

      function testSave() {
         $s = self::sess();

         $s->foo = 'bar';
         $force_write = $s->forceWrite();

         $id = $s->getID();
         $sql = 'SELECT data FROM alo_session WHERE id=?';
         $sql_params = [$id];
         $sess_fetched = \Alo::$db->prepQuery($sql, $sql_params, [
            mySQL::V_CACHE => false
         ]);

         $this->assertNotEmpty($sess_fetched, _unit_dump([
            'sql'     => $sql,
            'params'  => $sql_params,
            'fetched' => $sess_fetched,
            'all'     => \Alo::$db->prepQuery('SELECT * FROM alo_session')
         ]));

         $sess_fetched = json_decode($sess_fetched[0]['data'], true);

         $this->assertArrayHasKey('foo', $sess_fetched, 'Fetched data didn\'t have the key set');
         $this->assertEquals('bar', $sess_fetched['foo']);
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
 