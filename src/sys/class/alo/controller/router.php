<?php

   namespace Alo\Controller;

   use Alo;
   use Alo\Exception\ControllerException as CE;
   use ReflectionClass;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Handles routing to the correct controller and method
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      class Router {

         /**
          * Pretty self-explanatory, isn't it?
          *
          * @var string
          */
         const CONTROLLER_NAMESPACE = '\Controller\\';

         /**
          * Delimiter used in the regex checking
          *
          * @var string
          */
         const PREG_DELIMITER = '~';
         /**
          * Static reference to the last instance of the class
          *
          * @var Router
          */
         static $this;
         /**
          * Default params for a route
          *
          * @var array
          */
         protected static $routeDefaults = [
            'dir'    => null,
            'method' => 'index',
            'args'   => []
         ];
         /**
          * The server name
          *
          * @var string
          */
         protected $serverName;
         /**
          * The server IP
          *
          * @var string
          */
         protected $serverAddr;
         /**
          * The port in use
          *
          * @var int
          */
         protected $port;
         /**
          * The remote address
          *
          * @var string
          */
         protected $remoteAddr;
         /**
          * The request scheme
          *
          * @var string
          */
         protected $requestScheme;
         /**
          * The raw path info
          *
          * @var string
          */
         protected $path;
         /**
          * Request method in use
          *
          * @var string
          */
         protected $requestMethod;
         /**
          * Directory name
          *
          * @var string
          */
         protected $dir;
         /**
          * Controller name
          *
          * @var string
          */
         protected $controller;
         /**
          * Method name
          *
          * @var string
          */
         protected $method;
         /**
          * Arguments to pass on to the method
          *
          * @var array
          */
         protected $methodArgs;
         /**
          * The error controller name
          *
          * @var string
          */
         protected $errController;
         /**
          * The default controller
          *
          * @var string
          */
         protected $defaultController;
         /**
          * The routes array
          *
          * @var array
          */
         protected $routes;
         /**
          * Whether we're dealing with a CLI request...
          *
          * @var boolean
          */
         protected $isCliRequest;
         /**
          * Whether we're dealing with an AJAX request
          *
          * @var boolean
          */
         protected $isAjaxRequest;

         /**
          * Initialises the router
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return Router
          */
         function init() {
            self::$this = &$this;

            return $this->initNoCall()->tryCall();
         }

         /**
          * Tries to call the appropriate class' method
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return Router
          * @throws CE If the controller is already the error controller
          * @uses   self::forceError()
          */
         protected function tryCall() {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $rc = $rm = $init = false;

            try {
               $rc = new ReflectionClass(self::CONTROLLER_NAMESPACE . $this->controller);

               //Must be abstract controller's subclass
               if(!$rc->isAbstract() &&
                  $rc->isSubclassOf('\Alo\Controller\AbstractController')
               ) {
                  $rm = $rc->getMethod($this->method);

                  //And a public method
                  if($rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic()) {
                     //Excellent. Instantiate!
                     $init = true;
                  } else {
                     $this->forceError();
                  }
               } else {
                  $this->forceError();
               }
            } catch(\ReflectionException $ex) {
               $this->forceError($ex->getMessage());
            }

            if($init) {
               \Log::debug('Initialising controller ' .
                           $this->controller .
                           '->' .
                           $this->method .
                           '(' .
                           implode(',', $this->methodArgs) .
                           ')');
               $controllerName  = self::CONTROLLER_NAMESPACE . $this->controller;
               Alo::$controller = new $controllerName;
               call_user_func_array([Alo::$controller, $this->method], $this->methodArgs);
            }

            return $this;
         }

         /**
          * Forces the error controller
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $msg Optionally, the error message thrown by ReflectionClass
          *                    or ReflectionMethod
          *
          * @return \Alo\Controller\Router
          * @throws CE If the controller is already the error controller
          * @uses   self::tryCall()
          */
         protected function forceError($msg = null) {
            if($this->controller != $this->errController) {
               \Log::debug('404\'d on path: ' .
                           $this->path .
                           '. Settings were as follows: dir: ' .
                           $this->dir .
                           ', class: '
                           .
                           $this->controller .
                           ', method: ' .
                           $this->method .
                           ', args: ' .
                           json_encode($this->methodArgs));

               $path = DIR_CONTROLLERS . strtolower($this->errController) . '.php';
               if(file_exists($path)) {
                  include_once $path;
               }

               $this->controller = $this->errController;
               $this->method     = 'error';
               $this->methodArgs = [404];
               $this->tryCall();
            } else {
               throw new CE('No route available and the error controller '
                            . 'is invalid.' . ($msg ? ' Exception message returned: '
                                                      . $msg : ''), CE::E_INVALID_ROUTE);
            }

            return $this;
         }

         /**
          * Same as init(), but without attempting to call the controller
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @throws CE When the config file is not found
          * @throws CE When $error_controller_class is not present in the config file
          * @throws CE When The default controller is not present in the config file
          * @throws CE When $routes is not a valid array
          * @throws CE When a route value is not an array.
          * @return Router
          */
         function initNoCall() {
            $this->isCliRequest  = php_sapi_name() == 'cli' || defined('STDIN');
            $this->isAjaxRequest = \get($_SERVER['HTTP_X_REQUESTED_WITH']) == 'XMLHttpRequest';

            return $this->initServerVars()
                        ->initPath()
                        ->initRoutes()
                        ->resolvePath();
         }

         /**
          * Resolves the controller/method path
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return Router
          */
         protected function resolvePath() {
            //Use the default controller if the path is unavailable
            if(!$this->path) {
               $filepath = DIR_CONTROLLERS . strtolower($this->defaultController) . '.php';

               if(file_exists($filepath)) {
                  include_once $filepath;
               }

               $this->controller = $this->defaultController;
               $this->method     = self::$routeDefaults['method'];
               $this->methodArgs = self::$routeDefaults['args'];
               $this->dir        = self::$routeDefaults['dir'];
            } else {
               $resolved = false;

               //Check if there's a route
               foreach($this->routes as $source => $dest) {
                  $sourceReplace = trim(str_replace(self::PREG_DELIMITER, '\\' . self::PREG_DELIMITER, $source), '/');
                  $regex         =
                     self::PREG_DELIMITER . '^' . $sourceReplace . '/?' . '$' . self::PREG_DELIMITER . 'is';

                  if(preg_match($regex, $this->path)) {
                     $resolved = true;
                     $explode  = explode('/', $this->path);

                     $this->dir        = $dest['dir'] ? $dest['dir'] . DIRECTORY_SEPARATOR : self::$routeDefaults['dir'];
                     $this->controller = isset($dest['class']) ? $dest['class'] : $explode[0];

                     //Remove controller
                     array_shift($explode);

                     //Set method
                     if($dest['method'] != self::$routeDefaults['method']) {
                        $this->method = $dest['method'];
                     } elseif(isset($explode[0])) {
                        $this->method = $explode[0];
                     } else {
                        $this->method = self::$routeDefaults['method'];
                     }

                     //Remove controller method
                     if(!empty($explode)) {
                        array_shift($explode);
                     }

                     //Set preliminary method args
                     if($dest['args'] != self::$routeDefaults['args']) {
                        $this->methodArgs = $dest['args'];
                     } elseif(!empty($explode)) {
                        $this->methodArgs = $explode;
                     } else {
                        $this->methodArgs = self::$routeDefaults['args'];
                     }

                     $replace = explode('/', preg_replace($regex, implode('/', $this->methodArgs), $this->path));

                     //Remove empties
                     foreach($replace as $k => $v) {
                        if($v == '') {
                           unset($replace[$k]);
                        }
                     }

                     $this->methodArgs = $replace;

                     break;
                  }
               }

               if(!$resolved) {
                  //If not, assume the path is controller/method/arg1...
                  $path = explode('/', $this->path);

                  $this->dir        = null;
                  $this->controller = array_shift($path);
                  $this->method     = empty($path) ? self::$routeDefaults['method'] : array_shift($path);
                  $this->methodArgs = $path;
               }

               $filepath =
                  DIR_CONTROLLERS . str_replace('/', DIRECTORY_SEPARATOR, $this->dir) . $this->controller . '.php';

               if(file_exists($filepath)) {
                  include_once $filepath;
               }
            }

            return $this;
         }

         /**
          * Initialises the routing variables
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws CE When the config file is not found
          * @throws CE When $error_controller_class is not present in the config file
          * @throws CE When The default controller is not present in the config file
          * @throws CE When $routes is not a valid array
          * @throws CE When a route value is not an array.
          * @return Router
          */
         protected function initRoutes() {
            $path = \Alo::loadConfig('router', true);

            if(!file_exists($path)) {
               throw new CE('Routing config file not found.', CE::E_CONFIG_NOT_FOUND);
            } else {
               require $path;

               if(!isset($errorControllerClass)) {
                  throw new CE('Error controller class not found in config file.', CE::E_ERR_NOT_FOUND);
               } elseif(!isset($defaultController)) {
                  throw new CE('$default_controller undefined in config file.', CE::E_DEFAULT_UNDEFINED);
               } elseif(!is_array(get($routes))) {
                  throw new CE('The routes variable must be an associative array', CE::E_MALFORMED_ROUTES);
               } else {
                  $this->errController     = $errorControllerClass;
                  $this->defaultController = $defaultController;

                  foreach($routes as $k => $v) {
                     if(is_array($v)) {
                        $this->routes[strtolower($k)] = array_merge(self::$routeDefaults, $v);
                     } else {
                        throw new CE('Route ' . $k . ' is not a valid array.', CE::E_MALFORMED_ROUTES);
                     }
                  }

                  \Log::debug('Routes initialised');
               }
            }

            return $this;
         }

         /**
          * Initialises the raw path variable
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return Router
          */
         protected function initPath() {
            if(isset($_SERVER['PATH_INFO'])) {
               $this->path = ltrim($_SERVER['PATH_INFO'], '/');
            } elseif(isset($_SERVER['argv'])) {
               //Shift off the "index.php" bit
               array_shift($_SERVER['argv']);
               $this->path = join(DIRECTORY_SEPARATOR, $_SERVER['argv']);
            } else {
               $this->path = '';
            }

            $this->path = strtolower($this->path);

            return $this;
         }

         /**
          * Initialises most server variables
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return Router
          */
         protected function initServerVars() {
            $this->port          = \get($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : null;
            $this->remoteAddr    = \get($_SERVER['REMOTE_ADDR']);
            $this->requestScheme = \get($_SERVER['REQUEST_SCHEME']);
            $this->requestMethod = \get($_SERVER['REQUEST_METHOD']);
            $this->serverAddr    = \get($_SERVER['SERVER_ADDR']);
            $this->serverName    = \get($_SERVER['SERVER_NAME']);

            return $this;
         }

         /**
          * Returns whether this is a CLI request
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return bool
          */
         function isCliRequest() {
            return $this->isCliRequest;
         }

         /**
          * Returns whether this is an AJAX request
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return bool
          */
         function isAjaxRequest() {
            return $this->isAjaxRequest;
         }

         /**
          * Returns the error controller name
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getErrController() {
            return $this->errController;
         }

         /**
          * Returns the controller method name
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getMethod() {
            return $this->method;
         }

         /**
          * Returns the controller name
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getController() {
            return $this->controller;
         }

         /**
          * Returns the request port used
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return int
          */
         function getPort() {
            return $this->port;
         }

         /**
          * Returns the directory name
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getDir() {
            return $this->dir;
         }

         /**
          * Returns the request remote IP
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getRemoteAddr() {
            return $this->remoteAddr;
         }

         /**
          * Returns the request method used
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getRequestMethod() {
            return $this->requestMethod;
         }

         /**
          * Returns the request scheme used
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getRequestScheme() {
            return $this->requestScheme;
         }

         /**
          * Returns the server internal IP
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getServerAddr() {
            return $this->serverAddr;
         }

         /**
          * Returns the server name
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getServerName() {
            return $this->serverName;
         }

         /**
          * Returns the request path
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function getPath() {
            return $this->path;
         }

         /**
          * Returns a string representation of the object data
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         function __toString() {
            return strip_tags(\lite_debug($this));
         }

      }
   }
