<?php

   use Alo\Cache\MemcachedWrapper;

   class MySQLTest extends \PHPUnit_Framework_TestCase {

      /**
       * @dataProvider definedProvider
       */
      function testDefined($key) {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
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
            ['ALO_MYSQL_CACHE_PREFIX'],
            ['ALO_MYSQL_CHARSET']
         ];
      }

      /**
       * @expectedException PDOException
       */
      function testInvalidConstructorCredentials() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         new Alo\Db\MySQL('127.0.0.1', 3306, 'bad_username', 'bad_password', 'bad_table');
      }

      function testPrepare() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $sql = self::new_mysql();

         self::create_sql();

         $this->assertInstanceOf('PDOStatement', $sql->prepare('INSERT INTO `test_table`(`key0`) VALUES (?)'));

         self::delete_sql();
      }

      protected static function new_mysql() {
         phpunit_debug('[MySQLTest] new_mysql() called');
         if(!Alo::$db || !(Alo::$db instanceof \Alo\Db\MySQL)) {
            Alo::$db = new Alo\Db\MySQL('127.0.0.1', 3306, 'root', '', 'phpunit');
         }

         return Alo::$db;
      }

      protected static function create_sql($cols = 1) {
         phpunit_debug('[MySQLTest] create_sql called');
         self::delete_sql();
         $sql = 'CREATE TABLE `test_table` (';

         for($i = 0; $i < $cols; $i++) {
            $sql .= '`key' . $i . '` TINYINT(3) UNSIGNED NOT NULL,';
         }

         self::new_mysql()->prepQuery($sql . 'PRIMARY KEY (`key0`));');
      }

      protected static function delete_sql() {
         phpunit_debug('[MySQLTest] delete_sql() called');
         self::new_mysql()->prepQuery('DROP TABLE IF EXISTS `test_table`');
      }

      function testInTransaction() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $db = self::new_mysql();

         $this->assertFalse($db->transactionActive(), 'Transaction was active');

         $db->beginTransaction();
         $this->assertTrue($db->transactionActive(), 'Transaction wasn\'t active');

         $db->commit();
         $this->assertFalse($db->transactionActive(), 'Transaction was active');
      }

      function testPrepQuery() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $db = self::new_mysql();
         self::create_sql();

         $db->prepQuery('INSERT INTO `test_table` VALUES (?), (?), (?)', [1, 2, 3]);
         $sel    = $db->prepQuery('SELECT * FROM `test_table` WHERE `key0` > ?', [1]);
         $expect = [
            ['key0' => '2'],
            ['key0' => '3']
         ];

         $this->assertEquals($expect,
                             $sel,
                             _unit_dump([
                                           'Insert query'  => 'INSERT INTO `test_table` VALUES (?), (?), (?)',
                                           'Insert params' => [1, 2, 3],
                                           'PrepQuery'     => 'SELECT * FROM `test_table` WHERE `key0` > ?',
                                           'PrepParams'    => [1],
                                           'Expected'      => $expect,
                                           'Actual'        => $sel
                                        ]));

         self::delete_sql();
      }

      function testAggregate() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $db = self::new_mysql();
         self::create_sql();

         $db->prepQuery('INSERT INTO `test_table` VALUES (1), (2), (3)');
         $ag = $db->aggregate('SELECT SUM(`key0`) FROM `test_table`');

         $this->assertEquals(6,
                             $ag,
                             _unit_dump([
                                           'PrepQuery'      => 'INSERT INTO `test_table` VALUES (1), (2), (3)',
                                           'AggregateQuery' => 'SELECT SUM(`key0`) FROM `test_table`',
                                           'Expected'       => 6,
                                           'Actual'         => $ag
                                        ]));

         self::delete_sql();
      }

      function testCache() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $db = self::new_mysql();
         $mc = self::mc();
         $mc->purge();

         self::create_sql();

         $prep_sql    = 'INSERT INTO `test_table` VALUES (?), (?), (?)';
         $prep_params = [1, 2, 3];
         $ag_sql      = 'SELECT SUM(`key0`) FROM `test_table`';
         $ag_settings = [
            Alo\Db\MySQL::V_CACHE => true,
            Alo\Db\MySQL::V_TIME  => 20
         ];

         $db->prepQuery($prep_sql, $prep_params);

         $agg = $db->aggregate($ag_sql, null, $ag_settings);

         $last_hash = $db->getLastHash();
         $get_all   = $mc->getAll();
         $get       = $mc->get($last_hash);

         $this->assertArrayHasKey($last_hash,
                                  $get_all,
                                  _unit_dump([
                                                'last_hash' => $last_hash,
                                                'get_all'   => $get_all,
                                             ]));

         $this->assertEquals($agg,
                             $get,
                             _unit_dump([
                                           'aggregate' => $agg,
                                           'get'       => $get,
                                        ]));

         self::delete_sql();
      }

      protected static function mc() {
         phpunit_debug('[MySQLTest] mc() called');
         if(!Alo::$cache || !(Alo::$cache instanceof MemcachedWrapper)) {
            Alo::$cache = new MemcachedWrapper();
         }

         return Alo::$cache;
      }
   }
