<?php

    namespace Alo\Exception;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * Cron-related exceptions
         *
         * @author Art <a.molcanovas@gmail.com>
         */
        class CronException extends AbstractException {

            /**
             * Code when the schedule expression is invalid
             *
             * @var int
             */
            const E_INVALID_EXPR = 101;

            /**
             * Code when one or more arguments are non-scalar
             *
             * @var int
             */
            const E_ARGS_NONSCALAR = 102;

        }
    }
