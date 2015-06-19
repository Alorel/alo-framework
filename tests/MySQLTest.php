<?php

   use Alo\Db\MySQL;

   class MySQLTest extends \PHPUnit_Framework_TestCase {

      /**
       * @dataProvider definedProvider
       */
      function testDefined($key) {
         $this->assertTrue(defined($key), $key . ' wasn\'t defined');
         ob_flush();
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
         ob_flush();
      }

      function testPrepare() {
         self::createSQL();

         $this->assertInstanceOf('PDOStatement', PhuGlobal::$mysql->prepare('INSERT INTO `test_table`(`key0`) VALUES (?)'));

         self::deleteSQL();
         ob_flush();
      }

      protected static function createSQL($cols = 1) {
         self::deleteSQL();
         $sql = 'CREATE TABLE `test_table` (';

         for($i = 0; $i < $cols; $i++) {
            $sql .= '`key' . $i . '` TINYINT(3) UNSIGNED NOT NULL,';
         }

         PhuGlobal::$mysql->prepQuery($sql . 'PRIMARY KEY (`key0`));');
         ob_flush();
      }

      protected static function deleteSQL() {
         PhuGlobal::$mysql->prepQuery('DROP TABLE IF EXISTS `test_table`');
         ob_flush();
      }

      function testInTransaction() {
         $this->assertFalse(PhuGlobal::$mysql->transactionActive(), 'Transaction was active');

         PhuGlobal::$mysql->beginTransaction();
         $this->assertTrue(PhuGlobal::$mysql->transactionActive(), 'Transaction wasn\'t active');

         PhuGlobal::$mysql->commit();
         $this->assertFalse(PhuGlobal::$mysql->transactionActive(), 'Transaction was active');
         ob_flush();
      }

      function testPrepQuery() {
         self::createSQL();

         PhuGlobal::$mysql->prepQuery('INSERT INTO `test_table` VALUES (?), (?), (?)', [1, 2, 3]);
         $sel = PhuGlobal::$mysql->prepQuery('SELECT * FROM `test_table` WHERE `key0` > ?', [1]);
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
         ob_flush();
      }

      function testAggregate() {
         self::createSQL();

         PhuGlobal::$mysql->prepQuery('INSERT INTO `test_table` VALUES (1), (2), (3)');
         $ag = PhuGlobal::$mysql->aggregate('SELECT SUM(`key0`) FROM `test_table`');

         $this->assertEquals(6,
                             $ag,
                             _unit_dump([
                                           'PrepQuery'      => 'INSERT INTO `test_table` VALUES (1), (2), (3)',
                                           'AggregateQuery' => 'SELECT SUM(`key0`) FROM `test_table`',
                                           'Expected'       => 6,
                                           'Actual'         => $ag
                                        ]));

         self::deleteSQL();
         ob_flush();
      }

      function testCache() {
//         if(!serverIsWindows()) {
         PhuGlobal::$mcWrapper->purge();

         self::createSQL();

         $prepSQL    = 'INSERT INTO `test_table` VALUES (?), (?), (?)';
         $prepParams = [1, 2, 3];
         $agSQL      = 'SELECT SUM(`key0`) FROM `test_table`';
         $agSettings = [
            MySQL::V_CACHE => true,
            MySQL::V_TIME  => 20
         ];

         PhuGlobal::$mysql->prepQuery($prepSQL, $prepParams);

         $agg = PhuGlobal::$mysql->aggregate($agSQL, null, $agSettings);

         $lastHash = PhuGlobal::$mysql->getLastHash();
         $getAll   = PhuGlobal::$mcWrapper->getAll();
         $get      = PhuGlobal::$mcWrapper->get($lastHash);

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
//         }
         ob_flush();
      }
   }
