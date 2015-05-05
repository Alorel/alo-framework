<?php

   namespace Alo;

   class CronTest extends \PHPUnit_Framework_TestCase {

      function testFunctionAvailable() {
         $this->assertTrue(function_exists('server_is_windows'));
      }

      function testClear() {
         if (!\server_is_windows()) {
            $c = new Cron();

            $c->appendCrontab('php foo.php')
               ->commit()
               ->reloadCrontab()
               ->clearCrontab()
               ->commit()
               ->reloadCrontab();

            $this->assertEmpty($c->getCrontab());
         }
      }

      function testAppend() {
         if (!\server_is_windows()) {
            $c = new Cron();

            $c->clearCrontab()
               ->commit()
               ->reloadCrontab()
               ->appendCrontab('php foo.php')
               ->commit()
               ->reloadCrontab();

            $this->assertNotEmpty($c->getCrontab());
         }
      }
   }
 