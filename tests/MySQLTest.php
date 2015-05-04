<?php

   use Alo\Cache\MemcachedWrapper;
   use Alo;

   class MySQLTest extends \PHPUnit_Framework_TestCase {

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

      function testInTransaction() {
         $db = self::new_mysql();

         $this->assertFalse($db->transactionActive());

         $db->beginTransaction();
         $this->assertTrue($db->transactionActive());

         $db->commit();
         $this->assertFalse($db->transactionActive());
      }

      protected static function create_sql($cols = 1) {
         $sql = 'CREATE TABLE IF NOT EXISTS test_table (';

         for ($i = 0; $i < $cols; $i++) {
            $sql .= '`key' . $i . '` TINYINT(3) UNSIGNED NOT NULL,';
         }

         self::new_mysql()->prepQuery($sql . 'PRIMARY KEY (`key0`));');
      }

      protected static function delete_sql() {
         self::new_mysql()->prepQuery('DROP TABLE IF EXISTS test_table');
      }

      function testPrepQuery() {
         $db = self::new_mysql();
         self::create_sql();

         $db->prepQuery('INSERT INTO test_table VALUES (?), (?), (?)', [1, 2, 3]);

         $this->assertEquals([
            ['key0' => '2'], ['key0' => '3']
         ], $db->prepQuery('SELECT * FROM test_table WHERE key0 > ?', [1]));

         self::delete_sql();
      }

      function testAggregate() {
         $db = self::new_mysql();
         self::create_sql();

         $db->prepQuery('INSERT INTO test_table VALUES (1), (2), (3)');

         $this->assertEquals(6, $db->aggregate('SELECT SUM(key0) FROM test_table'));

         self::delete_sql();
      }

      function testCache() {
         $db = self::new_mysql();
         $mc = self::mc();
         $mc->purge();

         self::create_sql();

         $db->prepQuery('INSERT INTO test_table VALUES (?), (?), (?)', [1, 2, 3]);

         $agg = $db->aggregate('SELECT SUM(key0) FROM test_table', null, [
            Alo\Db\MySQL::V_CACHE => true,
            Alo\Db\MySQL::V_TIME  => 20
         ]);

         $last_hash = $db->getLastHash();

         $this->assertArrayHasKey($last_hash, $mc->getAll());
         $this->assertEquals($agg, $mc->get($last_hash));

         self::delete_sql();
      }
   }
 