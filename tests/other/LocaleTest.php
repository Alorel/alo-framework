<?php

    class LocaleTest extends PHPUnit_Framework_TestCase {

        function testDefined() {
            $arr = ['ALO_LOCALE_DEFAULT',
                    'ALO_LOCALE_GLOBAL',
                    'ALO_LOCALE_FETCH_ALL',
                    'ALO_LOCALE_CACHE_TIME'];
            foreach ($arr as $d) {
                $this->assertTrue(defined($d), $d . ' was not defined');
            }
        }

        function testEquals() {
            $expected = ['glob_one'  => 'one',
                         'glob_two'  => 'two',
                         'loc_three' => 'three',
                         'loc_four'  => 'keturi'];

            $actual = PhuGlobal::$locale->getAll();

            $this->assertEquals($expected, $actual, _unit_dump(['expected' => $expected,
                                                                'actual'   => $actual]));
        }
    }
