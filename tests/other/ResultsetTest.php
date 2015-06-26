<?php

    use Alo\Db\Resultset;

    class ResultsetTest extends PHPUnit_Framework_TestCase {

        private static $spec = ['foo' => [Resultset::MOD_GT, 5],
                                'bar' => [Resultset::MOD_GET, -8]];
        /** @var Resultset */
        private $set;
        private $dataArray;

        function __construct($name = null, $a = [], $dn = '') {
            parent::__construct($name, $a, $dn);
            $this->dataArray = [];

            for ($i = 0; $i < 10; $i++) {
                $this->dataArray[] = ['foo' => $i,
                                      'bar' => $i * -1];
            }
        }

        function testToSQL() {
            $resultset = new Resultset([['foo' => 'bar', 'bar' => 'foo'],
                                        ['foo' => 3, 'bar' => '']]);

            $simpleExpect = 'INSERT INTO `phu`(`foo`,`bar`) VALUES(\'bar\',\'foo\'),(3,NULL)';
            $advExpect    = ['REPLACE INTO `phu2`(`foo`,`bar`) VALUES(:r0cfoo,:r0cbar),(:r1cfoo,:r1cbar)',
                             [':r0cfoo' => 'bar',
                              ':r0cbar' => 'foo',
                              ':r1cfoo' => 3,
                              ':r1cbar' => '']];

            $this->assertEquals($simpleExpect, $resultset->toInsertStatement('phu', 'INSERT', false));
            $this->assertEquals($advExpect, $resultset->toInsertStatement('phu2', 'REPLACE', true));

        }

        function testConstruct() {
            $this->reloadResultset();
            $this->assertEquals($this->dataArray, $this->set->toArray());
        }

        private function reloadResultset() {
            $this->set = new Resultset($this->dataArray);
        }

        function testAppendValue() {
            $this->reloadResultset();
            $this->set->appendValue(['bar' => 'bb'], self::$spec);

            $this->assertEquals([['foo' => 0, 'bar' => 0],
                                 ['foo' => 1, 'bar' => -1],
                                 ['foo' => 2, 'bar' => -2],
                                 ['foo' => 3, 'bar' => -3],
                                 ['foo' => 4, 'bar' => -4],
                                 ['foo' => 5, 'bar' => -5],
                                 ['foo' => 6, 'bar' => '-6bb'],
                                 ['foo' => 7, 'bar' => '-7bb'],
                                 ['foo' => 8, 'bar' => '-8bb'],
                                 ['foo' => 9, 'bar' => -9]], $this->set->toArray());
        }

        function testDecrementValue() {
            $this->reloadResultset();
            $this->set->decrementValue(['bar' => 10], self::$spec);

            $this->assertEquals([['foo' => 0, 'bar' => 0],
                                 ['foo' => 1, 'bar' => -1],
                                 ['foo' => 2, 'bar' => -2],
                                 ['foo' => 3, 'bar' => -3],
                                 ['foo' => 4, 'bar' => -4],
                                 ['foo' => 5, 'bar' => -5],
                                 ['foo' => 6, 'bar' => -16],
                                 ['foo' => 7, 'bar' => -17],
                                 ['foo' => 8, 'bar' => -18],
                                 ['foo' => 9, 'bar' => -9]], $this->set->toArray());
        }

        function testIncrementValue() {
            $this->reloadResultset();
            $this->set->incrementValue(['bar' => 10], self::$spec);

            $this->assertEquals([['foo' => 0, 'bar' => 0],
                                 ['foo' => 1, 'bar' => -1],
                                 ['foo' => 2, 'bar' => -2],
                                 ['foo' => 3, 'bar' => -3],
                                 ['foo' => 4, 'bar' => -4],
                                 ['foo' => 5, 'bar' => -5],
                                 ['foo' => 6, 'bar' => 4],
                                 ['foo' => 7, 'bar' => 3],
                                 ['foo' => 8, 'bar' => 2],
                                 ['foo' => 9, 'bar' => -9]], $this->set->toArray());
        }

        function testMultiplyValue() {
            $this->reloadResultset();
            $this->set->multiplyValue(['bar' => 10], self::$spec);

            $this->assertEquals([['foo' => 0, 'bar' => 0],
                                 ['foo' => 1, 'bar' => -1],
                                 ['foo' => 2, 'bar' => -2],
                                 ['foo' => 3, 'bar' => -3],
                                 ['foo' => 4, 'bar' => -4],
                                 ['foo' => 5, 'bar' => -5],
                                 ['foo' => 6, 'bar' => -60],
                                 ['foo' => 7, 'bar' => -70],
                                 ['foo' => 8, 'bar' => -80],
                                 ['foo' => 9, 'bar' => -9]], $this->set->toArray());
        }

        function testDeleteWhere() {
            $this->reloadResultset();
            $this->set->deleteWhere(self::$spec);
            $expected = [['foo' => 0, 'bar' => 0],
                         ['foo' => 1, 'bar' => -1],
                         ['foo' => 2, 'bar' => -2],
                         ['foo' => 3, 'bar' => -3],
                         ['foo' => 4, 'bar' => -4],
                         ['foo' => 5, 'bar' => -5],
                         ['foo' => 9, 'bar' => -9]];
            $actual   = array_values($this->set->toArray());

            $this->assertEquals($expected, $actual);
        }

        function testKeepWhere() {
            $this->reloadResultset();
            $this->set->keepWhere(self::$spec);
            $expected = [['foo' => 6, 'bar' => -6],
                         ['foo' => 7, 'bar' => -7],
                         ['foo' => 8, 'bar' => -8]];
            $actual   = array_values($this->set->toArray());

            $this->assertEquals($expected, $actual);
        }

        function testDivideValue() {
            $this->reloadResultset();
            $this->set->divideValue(['bar' => 10], self::$spec);

            $this->assertEquals([['foo' => 0, 'bar' => 0],
                                 ['foo' => 1, 'bar' => -1],
                                 ['foo' => 2, 'bar' => -2],
                                 ['foo' => 3, 'bar' => -3],
                                 ['foo' => 4, 'bar' => -4],
                                 ['foo' => 5, 'bar' => -5],
                                 ['foo' => 6, 'bar' => -0.6],
                                 ['foo' => 7, 'bar' => -0.7],
                                 ['foo' => 8, 'bar' => -0.8],
                                 ['foo' => 9, 'bar' => -9]], $this->set->toArray());
        }

        function testGetWhere() {
            $this->reloadResultset();

            $this->assertEquals([['foo' => 6, 'bar' => -6],
                                 ['foo' => 7, 'bar' => -7],
                                 ['foo' => 8, 'bar' => -8]], array_values($this->set->getWhere(self::$spec)));
        }

        function testPrependValue() {
            $this->reloadResultset();
            $this->set->prependValue(['bar' => 'bb'], self::$spec);

            $this->assertEquals([['foo' => 0, 'bar' => 0],
                                 ['foo' => 1, 'bar' => -1],
                                 ['foo' => 2, 'bar' => -2],
                                 ['foo' => 3, 'bar' => -3],
                                 ['foo' => 4, 'bar' => -4],
                                 ['foo' => 5, 'bar' => -5],
                                 ['foo' => 6, 'bar' => 'bb-6'],
                                 ['foo' => 7, 'bar' => 'bb-7'],
                                 ['foo' => 8, 'bar' => 'bb-8'],
                                 ['foo' => 9, 'bar' => -9]], $this->set->toArray());
        }

        function testSetValue() {
            $this->reloadResultset();
            $this->set->setValue(['bar' => 'bb'], self::$spec);

            $this->assertEquals([['foo' => 0, 'bar' => 0],
                                 ['foo' => 1, 'bar' => -1],
                                 ['foo' => 2, 'bar' => -2],
                                 ['foo' => 3, 'bar' => -3],
                                 ['foo' => 4, 'bar' => -4],
                                 ['foo' => 5, 'bar' => -5],
                                 ['foo' => 6, 'bar' => 'bb'],
                                 ['foo' => 7, 'bar' => 'bb'],
                                 ['foo' => 8, 'bar' => 'bb'],
                                 ['foo' => 9, 'bar' => -9]], $this->set->toArray());
        }
    }
