<?php

    namespace Alo\Statics;

    class FormatTest extends \PHPUnit_Framework_TestCase {

        function testIPv4() {
            $true = ['111.1.11.111',
                     '0.0.0.0',
                     '255.255.255.255',
                     '255.255.255.255/32'];

            $false = ['255.255.255.255::823',
                      '-1.0.0.0',
                      'a.255.255.255',
                      [],
                      new \stdClass()];

            foreach ($true as $input) {
                $this->assertTrue(Format::isIpv4($input), _unit_dump($input));
            }

            foreach ($false as $input) {
                $this->assertFalse(Format::isIpv4($input), _unit_dump($input));
            }
        }

        function testIsJSON() {
            $true  = ['"foo"', 'true', 'false', '[1,2,3]', '[]', '{}', '{"foo":1}'];
            $false = ['foo', [], '{foo: bar}'];

            foreach ($true as $v) {
                $this->assertTrue(Format::isJSON($v), _unit_dump($v));
            }
            foreach ($false as $v) {
                $this->assertFalse(Format::isJSON($v), _unit_dump($v));
            }
        }
    }
