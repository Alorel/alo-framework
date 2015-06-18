<?php

   namespace Alo\Controller;

   class RouterTest extends \PHPUnit_Framework_TestCase {

      function testConfig() {
         $path = \Alo::loadConfig('router', true);
         require $path;

         /** @var string $errorControllerClass */
         /** @var string $errorControllerClass */
         /** @var array $errorControllerClass */
         $this->assertTrue(isset($errorControllerClass), '$error_controller_class not set');
         $this->assertTrue(isset($defaultController), '$default_controller not set');
         $this->assertTrue(isset($routes), '$routes not set');
         ob_flush();
      }

      /**
       * @dataProvider testEqualsProvider
       */
      function testEquals($method, $val) {
         $r = new Router();
         $r->initNoCall();
         $call = call_user_func([$r, $method]);

         $this->assertEquals($val,
                             $call,
                             _unit_dump([
                                           'method'   => $method,
                                           'expected' => $val,
                                           'actual'   => $call
                                        ]));
         ob_flush();
      }

      function testEqualsProvider() {
         return [
            ['getMethod', 'index'],
            ['isCliRequest', true],
            ['isAjaxRequest', false],
            ['getPath', ''],
            ['getPort', ''],
            ['getRemoteAddr', ''],
            ['getRequestMethod', ''],
            ['getRequestScheme', '']
         ];
      }

   }
