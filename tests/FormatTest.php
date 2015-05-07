<?php

   namespace Alo\Statics;

   class FormatTest extends \PHPUnit_Framework_TestCase {

      function testIPv4() {
         $true = [
            '111.1.11.111',
            '0.0.0.0',
            '255.255.255.255',
            '255.255.255.255/32'
         ];

         $false = [
            '255.255.255.255::823',
            '-1.0.0.0',
            'a.255.255.255',
            [],
            new \stdClass()
         ];

         foreach ($true as $input) {
            $this->assertTrue(Format::is_ipv4_ip($input));
         }

         foreach ($false as $input) {
            $this->assertFalse(Format::is_ipv4_ip($input));
         }
      }
   }
 