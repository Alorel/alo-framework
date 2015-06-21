<?php

    namespace Alo\Exception;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * Testing suite-related exceptions
         *
         * @author  Art <a.molcanovas@gmail.com>
         * @package TestingSuite
         */
        class TesterException extends AbstractException {

            /**
             * Code when the class or object is invalid
             *
             * @var int
             */
            const E_CLASS_INVALID = 101;

            /**
             * Code when the method or function name is invalid
             *
             * @var int
             */
            const E_NAME_INVALID = 102;
        }
    }
