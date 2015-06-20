<?php

    if (!defined('GEN_START')) {
        http_response_code(404);
    } elseif (!defined('LOG_LEVEL') || !defined('LOG_LEVEL_ERROR') || !defined('LOG_LEVEL_DEBUG') ||
              !defined('LOG_LEVEL_NONE') || !defined('LOG_LEVEL_WARNING') ||
              !in_array(LOG_LEVEL, [LOG_LEVEL_ERROR, LOG_LEVEL_DEBUG, LOG_LEVEL_NONE, LOG_LEVEL_WARNING], true)
    ) {
        echo 'Invalid LOG_LEVEL setting. Valid values are the framework constants LOG_LEVEL_ERROR, LOG_LEVEL_DEBUG and LOG_LEVEL_NONE!';
    } else {

        /**
         * Logs messages
         *
         * @author Art <a.molcanovas@gmail.com>
         */
        abstract class Log {

            /**
             * Identifier for error messages
             *
             * @var string
             */
            const MSG_ERROR = 'ERROR';

            /**
             * Identifier for debug messages
             *
             * @var string
             */
            const MSG_DEBUG = 'DEBUG';

            /**
             * Identifier for warning messages
             *
             * @var string
             */
            const MSG_WARNING = 'WARNING';

            /**
             * The logging level
             *
             * @var string
             */
            protected static $logLevel = LOG_LEVEL;

            /**
             * Today's date
             *
             * @var string
             */
            protected static $today;

            /**
             * Gets or sets the log level. If no parameter is passed, returns
             * self::$logLevel, if it's set and has a valid value, sets it and
             * returns TRUE;
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string|null $level The logging level
             *
             * @return boolean|string
             * @see    self::$logLevel
             */
            static function logLevel($level = null) {
                if (in_array($level, [LOG_LEVEL_DEBUG, LOG_LEVEL_ERROR, LOG_LEVEL_NONE, LOG_LEVEL_WARNING], true)) {
                    self::$logLevel = $level;

                    return true;
                } else {
                    return self::$logLevel;
                }
            }

            /**
             * Logs a debug level message
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $msg   The message
             * @param array  $trace optionally, supply the debug trace
             *
             * @return string The message you passed on
             */
            static function debug($msg, $trace = null) {
                if (self::$logLevel === LOG_LEVEL_DEBUG && is_scalar($msg)) {
                    self::doWrite($msg, self::MSG_DEBUG, $trace);
                }

                return $msg;
            }

            /**
             * Logs a warning level message
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $msg   The message
             * @param array  $trace optionally, supply the debug trace
             *
             * @return string The message you passed on
             */
            static function warning($msg, $trace = null) {
                if ((self::$logLevel === LOG_LEVEL_WARNING || self::$logLevel === LOG_LEVEL_DEBUG) && is_scalar($msg)) {
                    self::doWrite($msg, self::MSG_WARNING, $trace);
                }

                return $msg;
            }

            /**
             * Logs a error level message
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $msg   The message
             * @param array  $trace optionally, supply the debug trace
             *
             * @return string The message you passed on
             */
            static function error($msg, $trace = null) {
                if (self::$logLevel != LOG_LEVEL_NONE && is_scalar($msg)) {
                    self::doWrite($msg, self::MSG_ERROR, $trace);
                }

                return $msg;
            }

            /**
             * Performs the write operation
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $msg   The message to log
             * @param string $level The level of the message
             * @param array  $trace optionally, supply the debug trace
             *
             * @return boolean
             */
            protected static function doWrite($msg, $level, $trace = null) {
                $filepath = DIR_APP . 'logs' . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';
                ob_start();
                $fp = fopen($filepath, 'ab');
                ob_end_clean();

                if (!$fp) {
                    return false;
                } else {
                    if (!$trace || !is_array($trace)) {
                        $trace = debug_backtrace();
                        array_shift($trace);
                    }

                    if (defined('LOG_INTENSE') && LOG_INTENSE) {
                        $traceAppend = serialize($trace);
                    } else {
                        $xpl = explode(DIR_INDEX, isset($trace[0]['file']) ? $trace[0]['file'] : null);

                        $traceAppend = (isset($trace[0]['line']) ? $trace[0]['line'] : '[unknown line]') . ' @ "' .
                                       str_replace('"', '\"', isset($xpl[1]) ? $xpl[1] : $xpl[0]) . '"';
                    }

                    $message =
                        str_pad('[' . timestampPrecise() . ']', 25, ' ') . ' ' . str_pad($level, 5, ' ') . ' | "' .
                        str_replace('"', '\"', $msg) . '" | ' . $traceAppend . PHP_EOL;

                    flock($fp, LOCK_EX);
                    fwrite($fp, $message);
                    flock($fp, LOCK_UN);
                    fclose($fp);

                    ob_start();
                    chmod($filepath, '0666');
                    ob_end_clean();

                    return true;
                }
            }

        }
    }
