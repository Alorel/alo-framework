<?php

   namespace Alo\Validators;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * Form validator
    *
    * @author  Art <a.molcanovas@gmail.com>
    * @package Validators
    */
   class Form {

      /**
       * Defines a requirement as "email format"
       *
       * @var int
       */
      const R_EMAIL = 101;

      /**
       * Defines a requirement as "value required"
       *
       * @var int
       */
      const R_REQUIRED = 102;

      /**
       * Defines a requirement as "must be numeric"
       *
       * @var int
       */
      const R_NUMERIC = 103;

      /**
       * Defines a requirement as "minimum length"
       *
       * @var int
       */
      const R_LENGTH_MIN = 104;

      /**
       * Defines a requirement as "maximum length"
       *
       * @var int
       */
      const R_LENGTH_MAX = 105;

      /**
       * Defines a requirement as "must match regular expression"
       *
       * @var int
       */
      const R_REGEX = 106;

      /**
       * Defines a requirement as "must contain uppercase character"
       *
       * @var int
       */
      const R_CONTAIN_UPPERCASE = 107;

      /**
       * Defines a requirement as "must contain lowercase character"
       *
       * @var int
       */
      const R_CONTAIN_LOWERCASE = 108;

      /**
       * Defines a requirement as "must contain number"
       *
       * @var int
       */
      const R_CONTAIN_NUMBER = 109;

      /**
       * Defines a requirement as "must contain non-alphanumeric character"
       *
       * @var int
       */
      const R_CONTAIN_NONALPHANUM = 110;

      /**
       * Defines a requirement as "numeric value must be lower than"
       *
       * @var int
       */
      const R_VAL_LT = 111;

      /**
       * Defines a requirement as "numeric value must be greater than"
       *
       * @var int
       */
      const R_VAL_GT = 112;

      /**
       * Defines a requirement as "must be within a supplied range of values"
       *
       * @var int
       */
      const R_VAL_RANGE = 113;

      /**
       * Defines a requirement as "numeric value must be lower than or equal to"
       *
       * @var int
       */
      const R_VAL_LTE = 114;

      /**
       * Defines a requirement as "numeric value must be greater than or equal to"
       *
       * @var int
       */
      const R_VAL_GTE = 115;

      /**
       * Error when a value is non-scalar
       *
       * @var int
       */
      const E_NONSCALAR = 400;

      /**
       * Supplied data array
       *
       * @var array
       */
      protected $data;

      /**
       * Element requirements
       *
       * @var array
       */
      protected $binds;

      /**
       * Data array post-evaluation
       *
       * @var array
       */
      protected $evaluation;

      /**
       * Instantiates the class and loads the input array
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param array $input The input array
       */
      function __construct(array $input) {
         $this->data = $input;
         $this->binds = [];
         $this->evaluation = [
            'OK'         => true,
            'breakddown' => []
         ];
      }

      /**
       * Evaluates the data
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return Form
       */
      function evaluate() {
         $ok = true;

         foreach ($this->data as $data_key => $data_value) {
            $breakdown = &$this->evaluation['breakdown'][$data_key];

            if (!is_scalar($data_value)) {
               $breakdown = [
                  'OK'            => false,
                  'global_errors' => [self::E_NONSCALAR],
                  'breakdown'     => []
               ];
               $ok = false;
            } else {
               $local_ok = true;
               $breakdown['breakdown'] = [];

               if (isset($this->binds[$data_key])) {
                  foreach ($this->binds[$data_key] as $bind_key => $bind_value) {
                     $breakdown['breakdown'][$bind_key] = self::eval_param($data_value, $bind_key, $bind_value);

                     if (!$breakdown['breakdown'][$bind_key]) {
                        $local_ok = $ok = false;
                     }
                  }
               }

               $breakdown['OK'] = $local_ok;
               $breakdown['global_errors'] = [];
            }
         }

         $this->evaluation['OK'] = $ok;

         return $this;
      }

      /**
       * Evaluates an element against a requirement
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $data_value Element value
       * @param int    $bind_key   The requirement identifier constant
       * @param mixed  $bind_value The requirement specs if applicable
       * @return boolean
       */
      protected static function eval_param($data_value, $bind_key, $bind_value) {
         switch ($bind_key) {
            case self::R_CONTAIN_LOWERCASE:
               return (bool)preg_match('/[a-z]/', $data_value);
            case self::R_CONTAIN_NONALPHANUM:
               return !((bool)preg_match('/^[a-z0-9]+$/i', trim($data_value)));
            case self::R_CONTAIN_NUMBER:
               return (bool)preg_match('/[0-9]/', $data_value);
            case self::R_CONTAIN_UPPERCASE:
               return (bool)preg_match('/[A-Z]/', $data_value);
            case self::R_EMAIL:
               return (bool)preg_match('/^[a-z0-9_\.\+-]+@[a-z0-9-]+\.[a-z0-9-\.]+$/i', trim($data_value));
            case self::R_LENGTH_MAX:
               return strlen(trim($data_value)) <= $bind_value;
            case self::R_LENGTH_MIN:
               return strlen($data_value) >= $bind_value;
            case self::R_NUMERIC:
               return is_numeric(trim($data_value));
            case self::R_REGEX:
               return (bool)preg_match($bind_value, $data_value);
            case self::R_REQUIRED:
               return $data_value != '';
            case self::R_VAL_GT:
               return ((float)$data_value) > ((float)$bind_value);
            case self::R_VAL_GTE:
               return ((float)$data_value) >= ((float)$bind_value);
            case self::R_VAL_LT:
               return ((float)$data_value) < ((float)$bind_value);
            case self::R_VAL_LTE:
               return ((float)$data_value) <= ((float)$bind_value);
            case self::R_VAL_RANGE:
               return is_array($bind_value) ? in_array($data_value, $bind_value) : false;
         }

         return false;
      }

      /**
       * Binds a set of requirements to an element
       *
       * @author Art <a.molcanovas@gmail.com>
       * @param string $element      The element key
       * @param array  $requirements Associative array of requirements, where the keys are one of this class' R_*
       *                             constants and the values are TRUE, or, if applicable, the required values for that
       *                             test.
       * @return Form
       */
      function bind($element, $requirements) {
         $e = &$this->binds[$element];
         if (isset($e)) {
            $e = array_merge($e, $requirements);
         } else {
            $e = $requirements;
         }

         return $this;
      }

      /**
       * Returns the evaluation array
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return array
       */
      function getEvaluation() {
         return $this->evaluation;
      }

      /**
       * Returns the binds set
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return array
       */
      function getBinds() {
         return $this->binds;
      }

   }