<?php

   namespace Alo;

   use Alo\Exception\ProfilerException as PE;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {
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
          * Defines a parameter as "Request headers set"
          *
          * @var string
          */
         const P_HEADERS = 'headers';
         /**
          * Defines a parameter as "request path"
          *
          * @var string
          */
         const P_REQUEST_PATH = 'request_path';
         /**
          * Defines a parameter as "memory allocated to PHP script via emalloc()"
          *
          * @var string
          */
         const P_MEMORY_USAGE = 'memory_usage';
         /**
          * Defines a parameter as "real memory allocated to PHP script"
          *
          * @var string
          */
         const P_REAL_MEMORY_USAGE = 'real_memory_usage';
         /**
          * Defines a parameter as "diff"
          *
          * @var string
          */
         const P_DIFF = '_diff';
         /**
          * Static reference to the last instance of the class
          *
          * @var Profiler
          */
         static $this;
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
            self::$this  = &$this;
         }

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @return Profiler
          */
         static function profiler() {
            return new Profiler();
         }

         /**
          * Sets a profiler mark
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $identifier How to identify this mark
          *
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
               self::P_HEADERS           => \getallheaders(),
               self::P_REQUEST_PATH      => $r->getPath(),
               self::P_MEMORY_USAGE      => memory_get_usage(false),
               self::P_REAL_MEMORY_USAGE => memory_get_usage(true)
            ];

            return $this;
         }

         /**
          * Returns absolute microtime difference between the two marks
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $firstMark  The first mark identifier
          * @param string $secondMark The second mark identifier
          *
          * @throws PE When one of the marks cannot be found
          * @return float
          */
         function timeBetween($firstMark, $secondMark) {
            if(!isset($this->marks[$firstMark])) {
               throw new PE('The first mark could not be found.', PE::E_MARK_NOT_SET);
            } elseif(!isset($this->marks[$secondMark])) {
               throw new PE('The second mark could not be found.', PE::E_MARK_NOT_SET);
            } else {
               return abs($this->marks[$firstMark][self::P_MICROTIME] - $this->marks[$secondMark][self::P_MICROTIME]);
            }
         }

         /**
          * Returns the difference between the two marks, i.e. all key/value pairs in $secondMark that differ from those
          * of $firstMark
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $firstMark  The first mark identifier
          * @param string $secondMark The second mark identifier
          *
          * @throws PE When one of the marks cannot be found
          * @return array
          */
         function diff($firstMark, $secondMark) {
            if(!isset($this->marks[$firstMark])) {
               throw new PE('The first mark could not be found.', PE::E_MARK_NOT_SET);
            } elseif(!isset($this->marks[$secondMark])) {
               throw new PE('The second mark could not be found.', PE::E_MARK_NOT_SET);
            } else {
               //Hide illogical array to string conversion notices
               ob_start();
               $diff = array_diff_assoc($this->marks[$secondMark], $this->marks[$firstMark]);
               ob_end_clean();

               return $diff;
            }
         }

         /**
          * Shows the diff on the specified key
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $key        The key
          * @param string $firstMark  The first mark
          * @param string $secondMark The second mark
          *
          * @return array
          * @throws PE If the key isn't found in one or both marks
          */
         function diffOnKey($key, $firstMark, $secondMark) {
            if(!isset($this->marks[$firstMark])) {
               throw new PE('The first mark could not be found.', PE::E_MARK_NOT_SET);
            } elseif(!isset($this->marks[$secondMark])) {
               throw new PE('The second mark could not be found.', PE::E_MARK_NOT_SET);
            } elseif(!isset($this->marks[$firstMark][$key])) {
               throw new PE('Invalid $key.', PE::E_KEY_INVALID);
            } else {
               $fm = $this->marks[$firstMark][$key];
               $sm = $this->marks[$secondMark][$key];

               $ret = [$firstMark  => $fm,
                       $secondMark => $sm
               ];

               if(is_numeric($fm)) {
                  $ret[self::P_DIFF] = abs($fm - $sm);
               } elseif(is_array($fm)) {
                  $ret[self::P_DIFF] = array_diff_assoc($sm, $fm);
               } else {
                  $ret[self::P_DIFF] = '[values not numeric or arrays]';
               }

               return $ret;
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
   }
