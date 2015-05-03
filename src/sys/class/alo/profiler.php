<?php

   namespace Alo;

   use Alo\Exception\ProfilerException as PE;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * A code profiling class
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   class Profiler {

      /**
       * Defines a parameter as "microtime"
       *
       * @var string
       */
      const P_MICROTIME = 'microtime';

      /**
       * Defines a parameter as "session data"
       *
       * @var string
       */
      const P_SESSION_DATA = 'session_data';

      /**
       * Defines a parameter as "$_GET data set"
       *
       * @var string
       */
      const P_GET = '$_GET';

      /**
       * Defines a parameter as "$_POST data set"
       *
       * @var string
       */
      const P_POST = '$_POST';

      /**
       * Defines a parameter as "$_FILES data set"
       *
       * @var string
       */
      const P_FILES = '$_FILES';

      /**
       * Defines a parameter as "controller in use"
       *
       * @var string
       */
      const P_CONTROLLER = 'controller';

      /**
       * Defines a parameter as "controller method in use"
       *
       * @var string
       */
      const P_CONTROLLER_METHOD = 'controller_method';

      /**
       * Defines a parameter as "port in use"
       *
       * @var string
       */
      const P_PORT = 'port';

      /**
       * Defines a parameter as "request IP"
       *
       * @var string
       */
      const P_REMOTE_ADDR = 'remote_addr';

      /**
       * Defines a parameter as "request method"
       *
       * @var string
       */
      const P_REQUEST_METHOD = 'request_method';

      /**
       * Defines a parameter as "request scheme"
       *
       * @var string
       */
      const P_REQUEST_SCHEME = 'request_scheme';

      /**
       * Defines a parameter as "server internal IP"
       *
       * @var string
       */
      const P_SERVER_ADDR = 'server_addr';

      /**
       * Defines a parameter as "server name"
       *
       * @var string
       */
      const P_SERVER_NAME = 'server_name';

      /**
       * Request headers set
       *
       * @var string
       */
      const P_HEADERS = 'headers';

      /**
       * Marks set
       *
       * @var array
       */
      protected $marks;

      /**
       * Instantiates the class
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      function __construct() {
         $this->marks = [];
      }

      function __destruct() {
         echo \debug($GLOBALS);
      }

      /**
       * Sets a profiler mark
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $identifier How to identify this mark
       * @return Profiler
       */
      function mark($identifier) {
         $m = &$this->marks[$identifier];
         $r = &\Alo::$router;

         $m = [
            self::P_MICROTIME         => microtime(true),
            self::P_SESSION_DATA      => \Alo::$session ? \Alo::$session->getAll() : false,
            self::P_GET               => $_GET,
            self::P_POST              => $_POST,
            self::P_FILES             => $_FILES,
            self::P_CONTROLLER        => $r->getController(),
            self::P_CONTROLLER_METHOD => $r->getMethod(),
            self::P_PORT              => $r->getPort(),
            self::P_REMOTE_ADDR       => $r->getRemoteAddr(),
            self::P_REQUEST_METHOD    => $r->getRequestMethod(),
            self::P_REQUEST_SCHEME    => $r->getRequestScheme(),
            self::P_SERVER_ADDR       => $r->getServerAddr(),
            self::P_SERVER_NAME       => $r->getServerName(),
            self::P_HEADERS           => getallheaders()
         ];

         return $this;
      }

      /**
       * Returns absolute microtime difference between the two marks
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $first_mark  The first mark identifier
       * @param string $second_mark The second mark identifier
       * @throws PE When one of the marks cannot be found
       * @return float
       */
      function timeBetween($first_mark, $second_mark) {
         if (!isset($this->marks[$first_mark])) {
            throw new PE('The first mark could not be found.', PE::E_MARK_NOT_SET);
         } elseif (!isset($this->marks[$second_mark])) {
            throw new PE('The second mark could not be found.', PE::E_MARK_NOT_SET);
         } else {
            return abs($this->marks[$first_mark][self::P_MICROTIME] - $this->marks[$second_mark][self::P_MICROTIME]);
         }
      }

      /**
       * Returns the marks set, as well as their data
       *
       * @return array
       */
      function getMarks() {
         return $this->marks;
      }
   }