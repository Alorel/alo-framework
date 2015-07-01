<?php

    namespace Alo;

    use Exception;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * Handles autoloading, errors and exceptions
         *
         * @author Art <a.molcanovas@gmail.com>
         */
        abstract class Handler {

            /**
             * Whether CSS has been injected yet
             *
             * @var bool
             */
            protected static $cssInjected = false;

            /**
             * Injects CSS into the page to preffity our output
             *
             * @author Art <a.molcanovas@gmail.com>
             */
            protected static function injectCss() {
                if (!self::$cssInjected) {
                    self::$cssInjected = true;
                    include DIR_SYS . 'core' . DIRECTORY_SEPARATOR . 'error.css.php';
                }
            }

            /**
             * The error handler
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param int    $errno   The level of the error raised
             * @param string $errstr  The error message
             * @param string $errfile The filename that the error was raised in
             * @param int    $errline The line number the error was raised at
             */
            static function error($errno, $errstr, $errfile, $errline) {
                self::injectCss();
                $type = $label = $errno;

                switch ($errno) {
                    case E_NOTICE:
                    case E_USER_NOTICE:
                        $type  = 'NOTICE';
                        $label = 'info';
                        break;
                    case E_ERROR:
                    case E_USER_ERROR:
                    case E_COMPILE_ERROR:
                    case E_RECOVERABLE_ERROR:
                    case E_CORE_ERROR:
                        $type  = 'ERROR';
                        $label = 'danger';
                        break;
                    case E_WARNING:
                    case E_USER_WARNING:
                    case E_CORE_WARNING:
                        $type  = 'WARNING';
                        $label = 'warning';
                        break;
                }

                $f = explode(DIR_INDEX, $errfile);
                $f = isset($f[1]) ? $f[1] : $f[0];

                echo '<div style="text-align:center">' //BEGIN outer container
                     . '<div class="alo-err alert alert-' . $label . '">' //BEGIN inner container
                     . '<div>' //BEGIN header
                     . '<span
class="alo-bold">' . $type . ': ' . '</span><span>' . $errstr . '</span></div>'//END header
                     . '<div><span class="alo-bold">Raised in </span>' . '<span class="alo-uline">' . $f . '</span>';

                if ($errline) {
                    echo '<span> @ line </span><span class="alo-uline">' . $errline . '</span>';
                }

                echo '</div><span class="alo-bold">Backtrace:</span>';

                $trace = array_reverse(debug_backtrace());
                array_pop($trace);

                self::echoTrace($trace);

                echo '</div>'//END inner
                     . '</div>'; //END outer

                $trace = \debug_backtrace();
                array_shift($trace);
                \Log::error($errstr, $trace);
            }

            /**
             * Used to automatically load class,interface and trait files
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $name Class name
             */
            static function autoloader($name) {
                $name      = ltrim(strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $name)), '/') . '.php';
                $locations = [DIR_APP . 'class',
                              DIR_SYS . 'class',
                              DIR_APP . 'interface',
                              DIR_APP . 'traits'];

                foreach ($locations as $l) {
                    if (file_exists($l . DIRECTORY_SEPARATOR . $name)) {
                        include_once $l . DIRECTORY_SEPARATOR . $name;
                        break;
                    }
                }
            }

            /**
             * Echoes previous exceptions if applicable
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param null|\Exception $e The previous exception
             */
            protected static function echoPreviousExceptions($e) {
                if ($e instanceof Exception) {
                    echo '<div><span class="alo-bold">Preceded by </span><span>[' . $e->getCode() . ']: ' .
                         $e->getMessage() . ' @ <span class="alo-uline">' . $e->getFile() . '</span>\'s line ' .
                         $e->getLine() . '.</span></div>';

                    self::echoPreviousExceptions($e->getPrevious());
                }
            }

            /**
             * Echoes the debug backtrace
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $trace The backtrace
             */
            protected static function echoTrace($trace) {
                echo '<table class="table" border="1" style="border-color:#ddd">'//BEGIN table
                     . '<thead>'//BEGIN head
                     . '<tr>'//BEGIN head row
                     . '<th>#</th>'//Trace number
                     . '<th>Method</th>'//Method used
                     . '<th>Args</th>'//Method args
                     . '<th>Location</th>'//File
                     . '<th>Line</th>'//Line of code
                     . '</tr>'//END head row
                     . '</thead>'//END head
                     . '<tbody>'; //BEGIN table

                foreach ($trace as $k => $v) {
                    $func = $loc = $line = '';

                    if (isset($v['class'])) {
                        $func = $v['class'];
                    }
                    if (isset($v['type'])) {
                        $func .= $v['type'];
                    }
                    if (isset($v['function'])) {
                        $func .= $v['function'] . '()';
                    }
                    if (!$func) {
                        $func = '[unknown]';
                    }

                    if (isset($v['file'])) {
                        $loc = get(explode(DIR_INDEX, $v['file'])[1]);
                    }
                    if (isset($v['line'])) {
                        $line .= $v['line'];
                    }

                    echo '<tr>' //BEGIN row
                         . '<td>' . $k . '</td>' //Trace #
                         . '<td>' . $func . '</td>' //Method used
                         . '<td>' . //BEGIN args
                         (get($v['args']) ? debugLite($v['args']) : '<span class="label label-default">NONE</span>') .
                         '</td>' //END args
                         . '<td>' //BEGIN location
                         . ($loc ? $loc : '<span class="label label-default">???</span>') . '</td>'//END location
                         . '<td>'//BEGIN line
                         . ($line || $line == '0' ? $line : '<span class="label label-default">???</span>') . '</td>'
                         //END line
                         . '</tr>';
                }

                echo '</tbody>' . '</table>';
            }

            /**
             * Exception handler
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param \Exception $e The exception
             */
            static function ecxeption(\Exception $e) {
                self::injectCss();

                echo '<div style="text-align:center">' //BEGIN outer container
                     . '<div class="alo-err alert alert-danger">'//BEGIN inner container
                     . '<div>'//BEGIN header
                     . '<span class="alo-bold">Uncaught exception: </span><span>' . $e->getMessage() . '</span></div>'
                     //END header
                     //BEGIN raised
                     . '<div><span class="alo-bold">Raised in </span><span class="alo-uline">' . $e->getFile() .
                     '</span> @ line ' . $e->getLine() . '</div>' . '<div><span class="alo-bold">Code: </span><span>' .
                     $e->getCode() . '</span></div>';

                self::echoPreviousExceptions($e->getPrevious());

                echo '<span class="alo-bold">Backtrace:</span>';

                self::echoTrace($e->getTrace());

                echo '</div></div>'; //END inner/outer
            }
        }
    }
