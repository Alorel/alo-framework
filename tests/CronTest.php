<?php

   namespace Alo;

   use PHPUNIT_GLOBAL;

   class CronTest extends \PHPUnit_Framework_TestCase {

      function testFunctionAvailable() {
         $this->assertTrue(function_exists('server_is_windows'));
      }

      function testClear() {
         if(!server_is_windows()) {
            $initial = PHPUNIT_GLOBAL::$cron->getCrontab();

            PHPUNIT_GLOBAL::$cron->appendCrontab('php foo.php');

            $postAppend = PHPUNIT_GLOBAL::$cron->getCrontab();

            PHPUNIT_GLOBAL::$cron->commit();
            PHPUNIT_GLOBAL::$cron->reloadCrontab()
                                 ->clearCrontab();

            $postClear = PHPUNIT_GLOBAL::$cron->getCrontab();

            PHPUNIT_GLOBAL::$cron->commit();

            PHPUNIT_GLOBAL::$cron->reloadCrontab();

            $final = PHPUNIT_GLOBAL::$cron->getCrontab();

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
            $initial = PHPUNIT_GLOBAL::$cron->getCrontab();

            PHPUNIT_GLOBAL::$cron->clearCrontab()
                                 ->commit();

            PHPUNIT_GLOBAL::$cron->reloadCrontab();

            $postReload = PHPUNIT_GLOBAL::$cron->getCrontab();

            PHPUNIT_GLOBAL::$cron->appendCrontab('php foo.php');

            $postAppend = PHPUNIT_GLOBAL::$cron->getCrontab();

            PHPUNIT_GLOBAL::$cron->commit();

            PHPUNIT_GLOBAL::$cron->reloadCrontab();

            $final = PHPUNIT_GLOBAL::$cron->getCrontab();

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
            PHPUNIT_GLOBAL::$cron->autocommit(true)->clearCrontab()->appendCrontab('php foo.php');

            $get = PHPUNIT_GLOBAL::$cron->reloadCrontab()->getCrontab();

            $this->assertEquals(1, count($get), _unit_dump($get));

            PHPUNIT_GLOBAL::$cron->appendCrontab('php bar.php')->reloadCrontab();

            $this->assertEquals('* * * * * php foo.php', PHPUNIT_GLOBAL::$cron->getAtIndex(0));
            $this->assertEquals('* * * * * php bar.php', PHPUNIT_GLOBAL::$cron->getAtIndex(1));
         }
      }

   }
