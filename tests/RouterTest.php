<?php

   namespace Alo\Controller;

   class RouterTest extends \PHPUnit_Framework_TestCase {

      function testConfig() {
         $path = \Alo::loadConfig('router', true);
         require $path;

         $this->assertTrue(isset($error_controller_class), '$error_controller_class not set');
         $this->assertTrue(isset($default_controller), '$default_controller not set');
         $this->assertTrue(isset($routes), '$routes not set');
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
      }

      function testEqualsProvider() {
         return [
            ['getMethod', 'index'],
            ['is_cli_request', true],
            ['is_ajax_request', false],
            ['getPath', ''],
            ['getPort', ''],
            ['getRemoteAddr', ''],
            ['getRequestMethod', ''],
            ['getRequestScheme', '']
         ];
      }

   }
