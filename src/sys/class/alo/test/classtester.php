<?php

   namespace Alo\Test;

   use Alo\Exception\TesterException as TE;
   use ReflectionClass;

   if(!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * Tests classes for output or return values
    *
    * @author     Art <a.molcanovas@gmail.com>
    * @package    TestingSuite
    * @deprecated Since v1.1
    */
   class ClassTester extends AbstractTester {

      /**
       * The object in testing
       *
       * @var mixed
       */
      protected $obj;

      /**
       * Instantiates the tester
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param mixed $obj Optionally, set the object upon instantiation
       *
       * @throws TE When the Supplied object or its reflection/name is invalid.
       */
      function __construct($obj = null) {
         parent::__construct();
         if($obj) {
            $this->obj($obj);
         }
      }

      /**
       * If no parameter is passed returns the currently set object, otherwise sets it
       *
       * @param null|string|ReflectionClass|mixed $obj The object to set. Either an instance of the object, a
       *                                               ReflectionClass of the object or the object name including the
       *                                               namespace
       *
       * @return mixed|ClassTester $this if a parameter is passed, the currently tester object otherwise
       * @throws TE When the Supplied object or its reflection/name is invalid.
       */
      function obj($obj = null) {
         if($obj === null) {
            return $this->obj;
         } elseif($obj instanceof ReflectionClass) {
            $obj       = $obj->getName();
            $this->obj = new $obj;
         } elseif(is_object($obj)) {
            $this->obj = $obj;
         } elseif(is_string($obj)) {
            $this->obj = new $obj;
         } else {
            throw new TE('Supplied object or its reflection/name is invalid.', TE::E_CLASS_INVALID);
         }

         return $this;
      }

      /**
       * Returns the callable parameter for call_user_func_array()
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string $name Method name
       *
       * @return array
       */
      protected function getCallable($name) {
         return [$this->obj, $name];
      }
   }