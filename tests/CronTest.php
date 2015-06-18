<?php

   namespace Alo;

   use PhuGlobal;

   class CronTest extends \PHPUnit_Framework_TestCase {

      function testFunctionAvailable() {
         $this->assertTrue(function_exists('server_is_windows'));
      }

      function testClear() {
         if(!server_is_windows()) {
            $initial = PhuGlobal::$cron->getCrontab();

            PhuGlobal::$cron->appendCrontab('php foo.php');

            $postAppend = PhuGlobal::$cron->getCrontab();

            PhuGlobal::$cron->commit();
            PhuGlobal::$cron->reloadCrontab()
                                 ->clearCrontab();

            $postClear = PhuGlobal::$cron->getCrontab();

            PhuGlobal::$cron->commit();

            PhuGlobal::$cron->reloadCrontab();

            $final = PhuGlobal::$cron->getCrontab();

            $this->assertEmpty($final,
                               _unit_dump([
                                             'initial'    => $initial,
                                             'postAppend' => $postAppend,
                                             'postClear'  => $postClear,
                                             'final'      => $final
                                          ]));
         }
      }

      function testAppend() {
         if(!server_is_windows()) {
            $initial = PhuGlobal::$cron->getCrontab();

            PhuGlobal::$cron->clearCrontab()
                                 ->commit();

            PhuGlobal::$cron->reloadCrontab();

            $postReload = PhuGlobal::$cron->getCrontab();

            PhuGlobal::$cron->appendCrontab('php foo.php');

            $postAppend = PhuGlobal::$cron->getCrontab();

            PhuGlobal::$cron->commit();

            PhuGlobal::$cron->reloadCrontab();

            $final = PhuGlobal::$cron->getCrontab();

            $this->assertNotEmpty($final,
                                  _unit_dump([
                                                'initial'    => $initial,
                                                'postReload' => $postReload,
                                                'postAppend' => $postAppend,
                                                'final'      => $final
                                             ]));
         }
      }

      function testAutocommitAndGetAtIndex() {
         if(!\server_is_windows()) {
            PhuGlobal::$cron->autocommit(true)->clearCrontab()->appendCrontab('php foo.php');

            $get = PhuGlobal::$cron->reloadCrontab()->getCrontab();

            $this->assertEquals(1, count($get), _unit_dump($get));

            PhuGlobal::$cron->appendCrontab('php bar.php')->reloadCrontab();

            $this->assertEquals('* * * * * php foo.php', PhuGlobal::$cron->getAtIndex(0));
            $this->assertEquals('* * * * * php bar.php', PhuGlobal::$cron->getAtIndex(1));
         }
      }

   }
