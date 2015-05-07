<?php

   namespace Alo;

   use Alo\Statics\Format;

   class cURLTest extends \PHPUnit_Framework_TestCase {

      function testGet() {
         $c = new cURL('http://www.google.com');

         $exec = trim($c->exec()->get());
         $errno = $c->errno();
         $error = $c->error();

         $this->assertEquals(0, $errno, 'Errno was ' . $errno);
         $this->assertEquals('', $error, 'Error was ' . $error);
      }
   }
 