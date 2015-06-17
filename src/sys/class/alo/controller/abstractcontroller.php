<?php

   namespace Alo\Controller;

   if(!defined('GEN_START')) {
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
          * @param boolean $echo_on_destruct Whether to echo contents on object destruct
          *
          * @author Art <a.molcanovas@gmail.com>
          */
         function __construct($echo_on_destruct = true) {
            ob_start();
            $this->echoOnDestruct = (bool)$echo_on_destruct;

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
          * Forces a HTTP error page to be displayed instead
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param int $code The HTTP response code
          */
         protected function httpError($code = 404) {
            $this->echoOnDestruct = true;
            ob_end_clean();

            $controller = \Alo::$router->getErrController();

            \Alo::$controller = new $controller;
            /** @noinspection PhpUndefinedMethodInspection */
            \Alo::$controller->error($code);
            die();
         }

         /**
          * Closure operations
          *
          * @author Art <a.molcanovas@gmail.com>
          */
         function __destruct() {
            if($this->echoOnDestruct) {
               $ob = ob_get_clean();

               if(\Alo::$router->isCliRequest()) {
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
            if($switch === null) {
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
         protected function loadView($name, $params = [], $return = false) {
            $name = strtolower($name);

            if(substr($name, -4) == '.php') {
               $name = substr($name, 0, -4);
            }

            $path = DIR_APP . 'view' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $name) . '.php';

            if(!file_exists($path)) {
               php_error('View file for ' . $name . ' could not be found');
            } else {
               extract($params);

               if($return) {
                  ob_start();
               }

               //not include_once so the view can be reused
               include $path;

               if($return) {
                  return ob_get_clean();
               }
            }

            return null;
         }

      }
   }
