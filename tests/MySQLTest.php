<?php

   use Alo\Cache\MemcachedWrapper;

   class MySQLTest extends \PHPUnit_Framework_TestCase {

      /**
       * @dataProvider definedProvider
       */
      function testDefined($key) {
         $this->assertTrue(defined($key), $key . ' wasn\'t defined');
      }

      function definedProvider() {
         return [
            ['ALO_MYSQL_SERVER'],
            ['ALO_MYSQL_PORT'],
            ['ALO_MYSQL_DATABASE'],
            ['ALO_MYSQL_USER'],
            ['ALO_MYSQL_PW'],
            ['ALO_MYSQL_CACHE'],
            ['ALO_MYSQL_CACHE_PREFIX']
         ];
      }

      protected static function new_mysql() {
         if (!Alo::$db) {
            Alo::$db = new Alo\Db\MySQL('127.0.0.1', 3306, 'root', '', 'phpunit');
         }

         return Alo::$db;
      }

      protected static function mc() {
         if (!Alo::$cache) {
            Alo::$cache = new MemcachedWrapper();
         }

         return Alo::$cache;
      }

      protected static function create_sql($cols = 1) {
         self::delete_sql();
         $sql = 'CREATE TABLE test_table (';

         for ($i = 0; $i < $cols; $i++) {
            $sql .= '`key' . $i . '` TINYINT(3) UNSIGNED NOT NULL,';
         }

         self::new_mysql()->prepQuery($sql . 'PRIMARY KEY (`key0`));');
      }

      protected static function delete_sql() {
         self::new_mysql()->prepQuery('DROP TABLE IF EXISTS test_table');
      }

      /**
       * @expectedException PDOException
       */
      function testInvalidConstructorCredentials() {
         new Alo\Db\MySQL('127.0.0.1', 3306, 'bad_username', 'bad_password', 'bad_table');
      }

      function testPrepare() {
         $sql = self::new_mysql();

         self::create_sql();

         $this->assertInstanceOf('PDOStatement', $sql->prepare('INSERT INTO test_table(key0) VALUES (?)'));

         self::delete_sql();
      }

      function testInTransaction() {
         $db = self::new_mysql();

         $this->assertFalse($db->transactionActive(), 'Transaction was active');

         $db->beginTransaction();
         $this->assertTrue($db->transactionActive(), 'Transaction wasn\'t active');

         $db->commit();
         $this->assertFalse($db->transactionActive(), 'Transaction was active');
      }

      function testPrepQuery() {
         $db = self::new_mysql();
         self::create_sql();

         $db->prepQuery('INSERT INTO test_table VALUES (?), (?), (?)', [1, 2, 3]);
         $sel = $db->prepQuery('SELECT * FROM test_table WHERE key0 > ?', [1]);
         $expect = [
            ['key0' => '2'], ['key0' => '3']
         ];

         $this->assertEquals($expect, $sel, _unit_dump([
            'Insert query'  => 'INSERT INTO test_table VALUES (?), (?), (?)',
            'Insert params' => [1, 2, 3],
            'PrepQuery'     => 'SELECT * FROM test_table WHERE key0 > ?',
            'PrepParams'    => [1],
            'Expected'      => $expect,
            'Actual'        => $sel
         ]));

         self::delete_sql();
      }

      function testAggregate() {
         $db = self::new_mysql();
         self::create_sql();

         $db->prepQuery('INSERT INTO test_table VALUES (1), (2), (3)');
         $ag = $db->aggregate('SELECT SUM(key0) FROM test_table');

         $this->assertEquals(6, $ag, _unit_dump([
            'PrepQuery'      => 'INSERT INTO test_table VALUES (1), (2), (3)',
            'AggregateQuery' => 'SELECT SUM(key0) FROM test_table',
            'Expected'       => 6,
            'Actual'         => $ag
         ]));

         self::delete_sql();
      }

      function testCache() {
         $db = self::new_mysql();
         $mc = self::mc();
         $mc->purge();

         self::create_sql();

         $prep_sql = 'INSERT INTO test_table VALUES (?), (?), (?)';
         $prep_params = [1, 2, 3];
         $ag_sql = 'SELECT SUM(key0) FROM test_table';
         $ag_settings = [
            Alo\Db\MySQL::V_CACHE => true,
            Alo\Db\MySQL::V_TIME  => 20
         ];

         $db->prepQuery($prep_sql, $prep_params);

         $agg = $db->aggregate($ag_sql, null, $ag_settings);

         $last_hash = $db->getLastHash();
         $get_all = $mc->getAll();
         $get = $mc->get($last_hash);

         $this->assertArrayHasKey($last_hash, $get_all, _unit_dump([
            'last_hash' => $last_hash,
            'get_all'   => $get_all,
         ]));

         $this->assertEquals($agg, $get, _unit_dump([
            'aggregate' => $agg,
            'get'       => $get,
         ]));

         self::delete_sql();
      }
   }
 