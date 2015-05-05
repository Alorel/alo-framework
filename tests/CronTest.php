<?php

   namespace Alo;

   class CronTest extends \PHPUnit_Framework_TestCase {

      function testFunctionAvailable() {
         $this->assertTrue(function_exists('server_is_windows'));
      }

      function testClear() {
         if (!\server_is_windows()) {
            $c = new Cron();

            $initial = $c->getCrontab();

            $c->appendCrontab('php foo.php');

            $post_append = $c->getCrontab();

            $c->commit()
               ->reloadCrontab()
               ->clearCrontab();

            $post_clear = $c->getCrontab();

            $c->commit()->reloadCrontab();

            $final = $c->getCrontab();

            $this->assertEmpty($final, _unit_dump([
               'initial'     => $initial,
               'post_append' => $post_append,
               'post_clear'  => $post_clear,
               'final'       => $final
            ]));
         }
      }

      function testAppend() {
         if (!\server_is_windows()) {
            $c = new Cron();

            $initial = $c->getCrontab();

            $c->clearCrontab()
               ->commit()
               ->reloadCrontab();

            $post_reload = $c->getCrontab();

            $c->appendCrontab('php foo.php');

            $post_append = $c->getCrontab();

            $c->commit()
               ->reloadCrontab();

            $final = $c->getCrontab();

            $this->assertNotEmpty($final, _unit_dump([
               'initial'     => $initial,
               'post_reload' => $post_reload,
               'post_append' => $post_append,
               'final'       => $final
            ]));
         }
      }
   }
 