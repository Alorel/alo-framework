<?php

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        ob_start();
        require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'log.php';
        include_once DIR_SYS . 'external' . DIRECTORY_SEPARATOR . 'kint' . DIRECTORY_SEPARATOR . 'Kint.class.php';
        require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'handler.php';

        spl_autoload_register('\Alo\Handler::autoloader');
        if (!defined('PHPUNIT_RUNNING')) {
            set_error_handler('\Alo\Handler::error', ini_get('error_reporting'));
            set_exception_handler('\Alo\Handler::ecxeption');
        }

        /**
         * A shortcut to isset($var) ? $var : null
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param mixed $var The variable to "return"
         *
         * @return mixed
         */
        function get(&$var) {
            return isset($var) ? $var : null;
        }

        /**
         * Returns a debug string of the passed on variables
         *
         * @return string
         */
        function debug() {
            if (!Kint::enabled()) {
                return null;
            } else {
                ob_start();
                $args = func_get_args();
                call_user_func_array(['Kint', 'dump'], $args);

                return ob_get_clean();
            }
        }

        /**
         * Returns a lite debug string of passed on variables
         *
         * @return string
         */
        function debugLite() {
            if (!Kint::enabled()) {
                return '';
            } else {
                ob_start();
                $argv = func_get_args();
                echo '<pre>';
                foreach ($argv as $k => $v) {
                    $k && print("\n\n");
                    echo s($v);
                }
                echo '</pre>' . "\n";

                return ob_get_clean();
            }
        }

        /**
         * Returns a very precise timestamp
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param float $microtime Optionally, supply your own microtime
         *
         * @return string Y-m-d H:i:s:{milliseconds}
         */
        function timestampPrecise($microtime = null) {
            if (!$microtime) {
                $microtime = microtime(true);
            }
            $t = explode('.', $microtime);

            return date('Y-m-d H:i:s', $t[0]) . ':' . round($t[1] / 10);
        }

        if (!function_exists('getallheaders')) {

            /**
             * Implement getallheaders() for non-apache servers
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return array
             */
            function getallheaders() {
                $headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] =
                            $value;
                    }
                }

                return $headers;
            }
        }

        /**
         * Triggers a PHP-level error with the level E_USER_ERROR
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $msg Error message
         *
         * @link   http://php.net/manual/en/function.trigger-error.php
         * @return bool
         */
        function phpError($msg) {
            return trigger_error($msg, E_USER_ERROR);
        }

        /**
         * Triggers a PHP-level error with the level E_USER_WARNING
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $msg Error message
         *
         * @link   http://php.net/manual/en/function.trigger-error.php
         * @return bool
         */
        function phpWarning($msg) {
            return trigger_error($msg, E_USER_WARNING);
        }

        /**
         * Triggers a PHP-level error with the level E_USER_NOTICE
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $msg Error message
         *
         * @link   http://php.net/manual/en/function.trigger-error.php
         * @return bool
         */
        function phpNotice($msg) {
            return trigger_error($msg, E_USER_NOTICE);
        }

        /**
         * Triggers a PHP-level error with the level E_USER_DEPRECATED
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $msg Error message
         *
         * @link   http://php.net/manual/en/function.trigger-error.php
         * @return bool
         */
        function phpDeprecated($msg) {
            return trigger_error($msg, E_USER_DEPRECATED);
        }

        require_once DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'alo.php';

        if (!defined('PHPUNIT_RUNNING')) {
            Alo::$router = new Alo\Controller\Router();
            Alo::includeonceifexists(DIR_APP . 'core' . DIRECTORY_SEPARATOR . 'autoload.php');
            Alo::$router->init();
        } else {
            Alo::includeonceifexists(DIR_APP . 'core' . DIRECTORY_SEPARATOR . 'autoload.php');
        }
    }
