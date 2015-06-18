<?php

   use Alo\Db\MySQL;

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
            ['ALO_MYSQL_CACHE_PREFIX'],
            ['ALO_MYSQL_CHARSET']
         ];
      }

      /**
       * @expectedException PDOException
       */
      function testInvalidConstructorCredentials() {
         new MySQL('127.0.0.1', 3306, 'bad_username', 'bad_password', 'bad_table');
      }

      function testPrepare() {
         self::createSQL();

         $this->assertInstanceOf('PDOStatement', PHPUNIT_GLOBAL::$mysql->prepare('INSERT INTO `test_table`(`key0`) VALUES (?)'));

         self::deleteSQL();
      }

      protected static function createSQL($cols = 1) {
         self::deleteSQL();
         $sql = 'CREATE TABLE `test_table` (';

         for($i = 0; $i < $cols; $i++) {
            $sql .= '`key' . $i . '` TINYINT(3) UNSIGNED NOT NULL,';
         }

         PHPUNIT_GLOBAL::$mysql->prepQuery($sql . 'PRIMARY KEY (`key0`));');
      }

      protected static function deleteSQL() {
         PHPUNIT_GLOBAL::$mysql->prepQuery('DROP TABLE IF EXISTS `test_table`');
      }

      function testInTransaction() {
         $this->assertFalse(PHPUNIT_GLOBAL::$mysql->transactionActive(), 'Transaction was active');

         PHPUNIT_GLOBAL::$mysql->beginTransaction();
         $this->assertTrue(PHPUNIT_GLOBAL::$mysql->transactionActive(), 'Transaction wasn\'t active');

         PHPUNIT_GLOBAL::$mysql->commit();
         $this->assertFalse(PHPUNIT_GLOBAL::$mysql->transactionActive(), 'Transaction was active');
      }

      function testPrepQuery() {
         self::createSQL();

         PHPUNIT_GLOBAL::$mysql->prepQuery('INSERT INTO `test_table` VALUES (?), (?), (?)', [1, 2, 3]);
         $sel = PHPUNIT_GLOBAL::$mysql->prepQuery('SELECT * FROM `test_table` WHERE `key0` > ?', [1]);
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

         self::deleteSQL();
      }

      function testAggregate() {
         self::createSQL();

         PHPUNIT_GLOBAL::$mysql->prepQuery('INSERT INTO `test_table` VALUES (1), (2), (3)');
         $ag = PHPUNIT_GLOBAL::$mysql->aggregate('SELECT SUM(`key0`) FROM `test_table`');

         $this->assertEquals(6,
                             $ag,
                             _unit_dump([
                                           'PrepQuery'      => 'INSERT INTO `test_table` VALUES (1), (2), (3)',
                                           'AggregateQuery' => 'SELECT SUM(`key0`) FROM `test_table`',
                                           'Expected'       => 6,
                                           'Actual'         => $ag
                                        ]));

         self::deleteSQL();
      }

      function testCache() {
         if(!server_is_windows()) {
            PHPUNIT_GLOBAL::$mcWrapper->purge();

            self::createSQL();

            $prepSQL    = 'INSERT INTO `test_table` VALUES (?), (?), (?)';
            $prepParams = [1, 2, 3];
            $agSQL      = 'SELECT SUM(`key0`) FROM `test_table`';
            $agSettings = [
               MySQL::V_CACHE => true,
               MySQL::V_TIME  => 20
            ];

            PHPUNIT_GLOBAL::$mysql->prepQuery($prepSQL, $prepParams);

            $agg = PHPUNIT_GLOBAL::$mysql->aggregate($agSQL, null, $agSettings);

            $lastHash = PHPUNIT_GLOBAL::$mysql->getLastHash();
            $getAll   = PHPUNIT_GLOBAL::$mcWrapper->getAll();
            $get      = PHPUNIT_GLOBAL::$mcWrapper->get($lastHash);

            $this->assertArrayHasKey($lastHash,
                                     $getAll,
                                     _unit_dump([
                                                   'lastHash' => $lastHash,
                                                   'getAll'   => $getAll,
                                                ]));

            $this->assertEquals($agg,
                                $get,
                                _unit_dump([
                                              'aggregate' => $agg,
                                              'get'       => $get,
                                           ]));

            self::deleteSQL();
         }
      }
   }
