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

      /**
       * Sets a profiler mark
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $identifier How to identify this mark
       * @return Profiler
       */
      function mark($identifier) {
         $m = &$this->marks[$identifier];

         $m[self::P_MICROTIME] = microtime(true);

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