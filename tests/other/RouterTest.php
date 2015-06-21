<?php

    namespace Alo\Controller;

    class RouterTest extends \PHPUnit_Framework_TestCase {

        function testConfig() {
            $path = \Alo::loadConfig('router', true);
            require $path;

            /** @var string $errorControllerClass */
            /** @var string $defaultController */
            /** @var array $routes */
            $this->assertTrue(isset($errorControllerClass), '$error_controller_class not set');
            $this->assertTrue(isset($defaultController), '$default_controller not set');
            $this->assertTrue(isset($routes), '$routes not set');
        }

        /**
         * @dataProvider testEqualsProvider
         */
        function testEquals($method, $val) {
            $r = new Router();
            $r->initNoCall();
            $call = call_user_func([$r, $method]);

            $this->assertEquals($val, $call, _unit_dump(['method'   => $method,
                                                         'expected' => $val,
                                                         'actual'   => $call]));
        }

        function testEqualsProvider() {
            return [['isCliRequest', true],
                    ['isAjaxRequest', false],
                    ['getPath', ''],
                    ['getPort', ''],
                    ['getRemoteAddr', ''],
                    ['getRequestMethod', ''],
                    ['getRequestScheme', '']];
        }

    }
