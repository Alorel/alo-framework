<?php

    namespace Alo\Controller;

    use Alo;
    use Alo\Security;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * The controller superclass
         *
         * @author Art <a.molcanovas@gmail.com>
         */
        abstract class AbstractController {

            /**
             * Static reference to the last instance of the class
             *
             * @var AbstractController
             */
            static $this;
            /**
             * Whether to echo contents on object destruct
             *
             * @var boolean
             */
            private $echoOnDestruct;

            /**
             * Instantiates the class
             *
             * @param boolean $echoOnDestruct Whether to echo contents on object destruct
             *
             * @author Art <a.molcanovas@gmail.com>
             */
            function __construct($echoOnDestruct = true) {
                ob_start();
                $this->echoOnDestruct = (bool)$echoOnDestruct;

                self::$this = &$this;
            }

            /**
             * Method to avoid errors. Should always be overridden.
             *
             * @author Art <a.molcanovas@gmail.com>
             */
            function index() {
                $this->httpError(404);
            }

            /**
             * Forces a HTTP error page to be displayed. This does not stop script execution, but prevents further output.
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param int $code The HTTP response code
             */
            protected function httpError($code = 404) {
                $this->echoOnDestruct = true;
                ob_clean();

                $controller           = Alo::$router->getErrController();
                $controllerNamespaced = '\Controller\\' . $controller;

                Alo::includeonceifexists(DIR_APP . 'controllers' . DIRECTORY_SEPARATOR . strtolower($controller) .
                                         '.php');

                if (!class_exists($controllerNamespaced, true)) {
                    http_response_code((int)$code);
                    echo 'HTTP ' . Security::unXss($code) . '.';
                } else {
                    Alo::$controller = new $controllerNamespaced;
                    /** @noinspection PhpUndefinedMethodInspection */
                    Alo::$controller->error($code);
                    ob_flush();
                    $this->echoOnDestruct = false;
                }
            }

            /**
             * Closure operations
             *
             * @author Art <a.molcanovas@gmail.com>
             */
            function __destruct() {
                if ($this->echoOnDestruct) {
                    $ob = ob_get_clean();

                    if (Alo::$router->isCliRequest()) {
                        $ob = strip_tags($ob);
                    }

                    echo $ob;
                } else {
                    ob_end_clean();
                }
            }

            /**
             * Returns if echoOnDestruct is true or false if called without a parameter
             * or sets it to true/false if the parameter is set
             *
             * @param boolean|null $switch The parameter
             *
             * @return boolean|AbstractController
             */
            protected function echoOnDestruct($switch = null) {
                if ($switch === null) {
                    return $this->echoOnDestruct;
                } else {
                    $this->echoOnDestruct = (bool)$switch;

                    return $this;
                }
            }

            /**
             * Loads a view
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string  $name   The name of the view without ".php".
             * @param array   $params Associative array of parameters to pass on to the view
             * @param boolean $return If set to TRUE, will return the view, if FALSE,
             *                        will echo it
             *
             * @return null|string
             */
            protected function loadView($name, $params = null, $return = false) {
                $name = strtolower($name);

                if (substr($name, -4) == '.php') {
                    $name = substr($name, 0, -4);
                }

                $path = DIR_APP . 'view' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $name) . '.php';

                if (!file_exists($path)) {
                    phpError('View file for ' . $name . ' could not be found');
                } else {
                    if (is_array($params) && !empty($params)) {
                        extract($params);
                    }

                    if ($return) {
                        ob_start();
                    }

                    //not include_once so the view can be reused
                    include $path;

                    if ($return) {
                        return ob_get_clean();
                    }
                }

                return null;
            }

        }
    }
