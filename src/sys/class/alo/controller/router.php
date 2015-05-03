<?php

   namespace Alo\Controller;

   use Alo;
   use Alo\Exception\ControllerException as CE;
   use ReflectionClass;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

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
       * The server name
       *
       * @var string
       */
      protected $server_name;

      /**
       * The server IP
       *
       * @var string
       */
      protected $server_addr;

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
      protected $remote_addr;

      /**
       * The request scheme
       *
       * @var string
       */
      protected $request_scheme;

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
      protected $request_method;

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
      protected $method_args;

      /**
       * The error controller name
       *
       * @var string
       */
      protected $err_controller;

      /**
       * The default controller
       *
       * @var string
       */
      protected $default_controller;

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
      protected $is_cli_request;

      /**
       * Whether we're dealing with an AJAX request
       *
       * @var boolean
       */
      protected $is_ajax_request;

      /**
       * Initialises the router
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return Router
       */
      function init() {
         $this->is_cli_request = php_sapi_name() == 'cli' || defined('STDIN');
         $this->is_ajax_request = \get($_SERVER['HTTP_X_REQUESTED_WITH']) == 'XMLHttpRequest';

         return $this->init_server_vars()
            ->init_path()
            ->init_routes()
            ->resolvePath()
            ->tryCall();
      }

      /**
       * Returns whether this is a CLI request
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return bool
       */
      function is_cli_request() {
         return $this->is_cli_request;
      }

      /**
       * Returns whether this is an AJAX request
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return bool
       */
      function is_ajax_request() {
         return $this->is_ajax_request;
      }

      /**
       * Forces the error controller
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $msg Optionally, the error message thrown by ReflectionClass
       *                    or ReflectionMethod
       * @return \Alo\Controller\Router
       * @throws CE If the controller is already the error controller
       * @uses   self::tryCall()
       */
      protected function forceError($msg = null) {
         if ($this->controller != $this->err_controller) {
            \Log::debug('Route for ' . $this->path . ' not found - forcing error controller');
            $this->controller = $this->err_controller;
            $this->method = 'error';
            $this->method_args = [404];
            $this->tryCall();
         } else {
            throw new CE('No route available and the error controller '
               . 'is invalid.' . ($msg ? ' Exception message returned: '
                  . $msg : ''), CE::E_INVALID_ROUTE);
         }

         return $this;
      }

      /**
       * Returns the error controller name
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      function getErrController() {
         return $this->err_controller;
      }

      /**
       * Tries to call the appropriate class' method
       *
       * @author Art <a.molcanovas@gmail.com>
       * @throws CE When the class/method is unavailable and the error controller
       *         is invalid
       * @return Router
       * @uses   self::forceError()
       */
      protected function tryCall() {
         try {
            $rc = new ReflectionClass($this->controller);

            //Must be abstract controller's subclass
            if (!$rc->isAbstract() &&
               $rc->isSubclassOf('\Alo\Controller\AbstractController')
            ) {
               $rm = $rc->getMethod($this->method);

               //And a public method
               if ($rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic()) {
                  //Excellent. Instantiate!
                  \Log::debug('Initialising controller');
                  Alo::$controller = new $this->controller;
                  call_user_func_array([Alo::$controller, $this->method], $this->method_args);
               } else {
                  $this->forceError();
               }
            } else {
               $this->forceError();
            }
         } catch (\Exception $ex) {
            $this->forceError($ex->getMessage());
         }

         return $this;
      }

      /**
       * Resolves the controller/method path
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return Router
       */
      protected function resolvePath() {
         //Use the default controller if the path is unavailable
         if (!$this->path) {
            $this->controller = self::CONTROLLER_NAMESPACE . $this->default_controller;
            $this->method = 'index';
            $this->method_args = [];
         } else {
            $resolved = false;

            //Check if there's a route
            foreach ($this->routes as $source => $dest) {
               $source_replace = trim(str_replace(self::PREG_DELIMITER, '\\'
                  . self::PREG_DELIMITER, $source), DIRECTORY_SEPARATOR);

               if (preg_match(self::PREG_DELIMITER . '^' . $source_replace . DIRECTORY_SEPARATOR . '?$' . self::PREG_DELIMITER . 'is', $this->path)) {
                  $replace = explode(DIRECTORY_SEPARATOR, preg_replace(self::PREG_DELIMITER . '^' . $source_replace . DIRECTORY_SEPARATOR . '?$' . self::PREG_DELIMITER . 'is', $dest, $this->path));
                  $resolved = true;

                  $this->controller = self::CONTROLLER_NAMESPACE . array_shift($replace);
                  $this->method = empty($replace) ? 'index' : array_shift($replace);
                  $this->method_args = $replace;

                  break;
               }
            }

            if (!$resolved) {
               //If not, assume the path is controller/method/arg1...
               $path = explode('/', $this->path);

               $this->controller = self::CONTROLLER_NAMESPACE . array_shift($path);
               $this->method = empty($path) ? 'index' : array_shift($path);
               $this->method_args = $path;
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
       * @throws CE When $routes[':default'] is not present
       * @throws CE When $routes is not a valid array
       * @throws CE When a route value is not a string
       * @return Router
       */
      protected function init_routes() {
         $path = \Alo::loadConfig('router', true);

         if (!file_exists($path)) {
            throw new CE('Routing config file not found.', CE::E_CONFIG_NOT_FOUND);
         } else {
            require $path;

            if (!isset($error_controller_class)) {
               throw new CE('Error controller class not found in config file.', CE::E_ERR_NOT_FOUND);
            } elseif (!isset($default_controller)) {
               throw new CE('$default_controller undefined in config file.', CE::E_DEFAULT_UNDEFINED);
            } elseif (!is_array($routes)) {
               throw new CE('The routes variable must be an associative array', CE::E_MALFORMED_ROUTES);
            } else {
               $this->err_controller = $error_controller_class;
               $this->default_controller = $default_controller;

               foreach ($routes as $k => $v) {
                  if (is_string($v)) {
                     $this->routes[strtolower($k)] = strtolower($v);
                  } else {
                     throw new CE('Route ' . $k . ' is invalid.', CE::E_MALFORMED_ROUTES);
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
      protected function init_path() {
         if (isset($_SERVER['PATH_INFO'])) {
            $this->path = ltrim($_SERVER['PATH_INFO'], '/');
         } elseif (isset($_SERVER['argv'])) {
            //Shift off the "index.php" bit
            array_shift($_SERVER['argv']);
            $this->path = join(DIRECTORY_SEPARATOR, $_SERVER['argv']);
         } else {
            $this->path = '';
         }

         return $this;
      }

      /**
       * Initialises most server variables
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return Router
       */
      protected function init_server_vars() {
         $this->port = get($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : null;
         $this->remote_addr = get($_SERVER['REMOTE_ADDR']);
         $this->request_scheme = get($_SERVER['REQUEST_SCHEME']);
         $this->request_method = get($_SERVER['REQUEST_METHOD']);
         $this->server_addr = get($_SERVER['SERVER_ADDR']);
         $this->server_name = get($_SERVER['SERVER_NAME']);

         return $this;
      }

      /**
       * Returns the controller method name
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      public function getMethod() {
         return $this->method;
      }

      /**
       * Returns the controller name
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      public function getController() {
         return $this->controller;
      }

      /**
       * Returns the request port used
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return int
       */
      public function getPort() {
         return $this->port;
      }

      /**
       * Returns the request remote IP
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      public function getRemoteAddr() {
         return $this->remote_addr;
      }

      /**
       * Returns the request method used
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      public function getRequestMethod() {
         return $this->request_method;
      }

      /**
       * Returns the request scheme used
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      public function getRequestScheme() {
         return $this->request_scheme;
      }

      /**
       * Returns the server internal IP
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      public function getServerAddr() {
         return $this->server_addr;
      }

      /**
       * Returns the server name
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      public function getServerName() {
         return $this->server_name;
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