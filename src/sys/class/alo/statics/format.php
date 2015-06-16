<?php

   namespace Alo\Statics;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Format validation statics
       *
       * @author  Art <a.molcanovas@gmail.com>
       * @package Statics
       */
      abstract class Format {

         /**
          * Defines an method as "serialize"
          *
          * @var int
          */
         const M_SERIALIZE = 0;

         /**
          * Defines a method as "json_encode"
          *
          * @var int
          */
         const M_JSON = 1;

         /**
          * Defines a method as "print_r"
          *
          * @var int
          */
         const M_PRINT_R = 2;

         /**
          * Checks whether the data is valid JSON
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param mixed $data The data to check
          *
          * @return boolean
          */
         static function isJSON($data) {
            if(!is_scalar($data)) {
               return false;
            } else {
               json_decode($data, true);

               return json_last_error() === JSON_ERROR_NONE;
            }
         }

         /**
          * Checks if the supplied string is a valid IPv4 IP
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $input The input
          *
          * @return bool
          */
         static function is_ipv4_ip($input) {
            if(!is_scalar($input)) {
               return false;
            } else {
               $e = explode('.', explode('/', $input)[0]);

               if(count($e) != 4) {
                  return false;
               } else {
                  foreach($e as $v) {
                     if(!is_numeric($v)) {
                        return false;
                     } else {
                        $v = (int)$v;

                        if($v < 0 || $v > 255) {
                           return false;
                        }
                     }
                  }
               }

               return true;
            }
         }

         /**
          * Makes output scalar. If $input is already scalar, simply returns it; otherwise uses a function specified in
          * $prettify_method to make the output scalar
          *
          * @param mixed $input           The input to scalarise
          * @param int   $prettify_method Function to use to make output scalar if $input isn't already scalar. See class
          *                               M_* constants.
          *
          * @return string
          */
         static function scalarOutput($input, $prettify_method = self::M_PRINT_R) {
            if(is_scalar($input)) {
               return $input;
            } else {
               switch($prettify_method) {
                  case self::M_JSON:
                     return json_encode($input);
                  case self::M_SERIALIZE:
                     return serialize($input);
                  default:
                     return print_r($input, true);
               }
            }
         }

         /**
          * Typecasts a variable to float or int if it's numeric
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param mixed   $var      The variable
          * @param boolean $boolMode Whether we're checking for boolean mode FULLTEXT search values
          *
          * @return int|float|mixed
          */
         static function makeNumeric($var, $boolMode = false) {
            if(is_numeric($var)) {
               if($boolMode) {
                  $first = substr($var, 0, 1);
                  if($first == '-' || $first == '+') {
                     return $var;
                  }
               }

               if(stripos($var, '.') === false) {
                  $var = (int)$var;
               } else {
                  $var = (float)$var;
               }
            }

            return $var;
         }

      }
   }
