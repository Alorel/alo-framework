<?php

    namespace Alo\Exception;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * Controller-related exceptions
         *
         * @author Art <a.molcanovas@gmail.com>
         */
        class ControllerException extends AbstractException {

            /**
             * Code when a default controller is not defined
             *
             * @var int
             */
            const E_DEFAULT_UNDEFINED = 100;

            /**
             * Code when the config file is not found
             *
             * @var int
             */
            const E_CONFIG_NOT_FOUND = 101;

            /**
             * Code when the error controller is not found
             *
             * @var int
             */
            const E_ERR_NOT_FOUND = 102;

            /**
             * Code when the routes array is malformed
             *
             * @var int
             */
            const E_MALFORMED_ROUTES = 103;

            /**
             * Code when there's no controller available and the error controller is
             * not set/invalid
             *
             * @var int
             */
            const E_INVALID_ROUTE = 104;

        }
    }
