<?php

   namespace Alo\Controller;

   class RouterTest extends \PHPUnit_Framework_TestCase {

      /**
       * @dataProvider testEqualsProvider
       */
      function testEquals($method, $val) {
         $r = new Router();
         $r->initNoCall();
         $call = call_user_func([$r, $method]);

         $this->assertEquals($val, $call, _unit_dump([
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
 