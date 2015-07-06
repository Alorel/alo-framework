<?php

    namespace Alo\Exception;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * ORM-related exceptions
         *
         * @author Art <a.molcanovas@gmail.com>
         */
        class ORMException extends AbstractException {

            /**
             * Code when a datatype is not valid
             *
             * @var int
             */
            const E_INVALID_DATATYPE = 101;

            /**
             * Code when the query is invalid
             * @var int
             */
            const E_INVALID_QUERY = 102;
        }
    }
