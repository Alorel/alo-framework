<?php

   namespace Alo;

   class CronTest extends \PHPUnit_Framework_TestCase {

      function testFunctionAvailable() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         $this->assertTrue(function_exists('server_is_windows'));
      }

      function testClear() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         if(!\server_is_windows()) {
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

            $this->assertEmpty($final,
                               _unit_dump([
                                             'initial'     => $initial,
                                             'post_append' => $post_append,
                                             'post_clear'  => $post_clear,
                                             'final'       => $final
                                          ]));
         }
      }

      function testAppend() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         if(!\server_is_windows()) {
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

            $this->assertNotEmpty($final,
                                  _unit_dump([
                                                'initial'     => $initial,
                                                'post_reload' => $post_reload,
                                                'post_append' => $post_append,
                                                'final'       => $final
                                             ]));
         }
      }

      function testAutocommitAndGetAtIndex() {
         phpunit_debug('[' . get_class($this) . ']: ' . json_encode(func_get_args()));
         if(!\server_is_windows()) {
            $c = new Cron();

            $c->autocommit(true)->clearCrontab()->appendCrontab('php foo.php');

            $get = $c->reloadCrontab()->getCrontab();

            $this->assertEquals(1, count($get), _unit_dump($get));

            $c->appendCrontab('php bar.php')->reloadCrontab();

            $this->assertEquals('* * * * * php foo.php', $c->getAtIndex(0));
            $this->assertEquals('* * * * * php bar.php', $c->getAtIndex(1));
         }
      }

   }
