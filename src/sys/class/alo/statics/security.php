<?php

   namespace Alo\Statics;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Handles hashing, tokens, randomising and other security operations
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      abstract class Security {

         /**
          * Defines the ascii charset subset as "the entire set"
          *
          * @var int
          */
         const ASCII_ALL = 0;

         /**
          * Defines the ascii charset subset as "only alphanumeric"
          *
          * @var int
          */
         const ASCII_ALPHANUM = 1;

         /**
          * Defines the ascii charset subset as "only non-alphanumeric"
          *
          * @var int
          */
         const ASCII_NONALPHANUM = 2;

         /**
          * Array of ASCII alphanumeric characters
          *
          * @var array
          */
         protected static $asciiAlphanum = ['a',
                                            'b',
                                            'c',
                                            'd',
                                            'e',
                                            'f',
                                            'g',
                                            'h',
                                            'i',
                                            'j',
                                            'k',
                                            'l',
                                            'm',
                                            'n',
                                            'o',
                                            'p',
                                            'q',
                                            'r',
                                            's',
                                            't',
                                            'u',
                                            'v',
                                            'w',
                                            'x',
                                            'y',
                                            'z',
                                            'A',
                                            'B',
                                            'C',
                                            'D',
                                            'E',
                                            'F',
                                            'G',
                                            'H',
                                            'I',
                                            'J',
                                            'K',
                                            'L',
                                            'M',
                                            'N',
                                            'O',
                                            'P',
                                            'Q',
                                            'R',
                                            'S',
                                            'T',
                                            'U',
                                            'V',
                                            'W',
                                            'X',
                                            'Y',
                                            'Z',
                                            0,
                                            1,
                                            2,
                                            3,
                                            4,
                                            5,
                                            6,
                                            7,
                                            8,
                                            9];
         /**
          * The rest of the ASCII charset
          *
          * @var array
          */
         protected static $asciiRest = [' ',
                                        '!',
                                        '"',
                                        '#',
                                        '$',
                                        '%',
                                        '\'',
                                        '(',
                                        ')',
                                        '*',
                                        '+',
                                        ',',
                                        '.',
                                        '/',
                                        ':',
                                        ';',
                                        '<',
                                        '=',
                                        '>',
                                        '?',
                                        '@',
                                        '[',
                                        '\\',
                                        ']',
                                        '^',
                                        '_',
                                        '`',
                                        '-',
                                        '{',
                                        '|',
                                        '}',
                                        '~'];

         /**
          * Escapes a string or array (recursively) from XSS attacks
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string|array $item The item to be escaped
          *
          * @return string|array
          */
         static function un_xss($item) {
            if(is_array($item)) {
               foreach($item as &$v) {
                  $v = self::un_xss($item);
               }

               return $item;
            } else {
               return is_scalar($item) ? htmlspecialchars($item, ENT_QUOTES | ENT_HTML5, 'UTF-8', false) : null;
            }
         }

         /**
          * Generates a token and sets it in session
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $token_name The token name
          * @param string $hash       Which hash algorithm to use
          *
          * @return string The generated token
          */
         static function tokenGet($token_name, $hash = 'md5') {
            $token = self::getUniqid($hash, 'token_' . $token_name);

            if(!\Alo::$session) {
               php_warning('Session handler not initialised or not assigned to \\Alo::$session. Token not saved in session.');
            } else {
               \Alo::$session->{$token_name} = $token;
            }

            return $token;
         }

         /**
          * Generates a unique identifier
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string     $hash    Hash algorithm
          * @param string|int $prefix  Prefix for the identifier
          * @param int        $entropy Number of pseudo bytes used in entropy
          *
          * @return string
          */
         static function getUniqid($hash = 'md5', $prefix = null, $entropy = 50) {
            $str = uniqid(mt_rand(PHP_INT_MIN, PHP_INT_MAX) . json_encode([
                                                                             $_COOKIE,
                                                                             $_REQUEST,
                                                                             $_FILES,
                                                                             $_ENV,
                                                                             $_GET,
                                                                             $_POST,
                                                                             $_SERVER
                                                                          ]),
                          true)
                   . $prefix . self::ascii_rand($entropy);

            if(function_exists('\openssl_random_pseudo_bytes')) {
               $str .= \openssl_random_pseudo_bytes($entropy);
            }

            return hash($hash, $str);
         }

         /**
          * Generates a string of random ASCII characters
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param int $length The length of the string
          * @param int $subset Which subset to use - see class' ASCII_* constants
          *
          * @return string
          */
         static function ascii_rand($length, $subset = self::ASCII_ALL) {
            switch($subset) {
               case self::ASCII_ALPHANUM:
                  $subset = self::$asciiAlphanum;
                  break;
               case self::ASCII_NONALPHANUM:
                  $subset = self::$asciiRest;
                  break;
               default:
                  $subset = array_merge(self::$asciiAlphanum, self::$asciiRest);
            }

            $count = count($subset) - 1;

            $r = '';

            for($i = 0; $i < $length; $i++) {
               $r .= $subset[mt_rand(0, $count)];
            }

            return $r;
         }

         /**
          * Checks if a token is valid
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $token_name The token name
          * @param array  $data_array Which data array to check. Defaults to $_POST
          *
          * @return bool TRUE if the token is valid, false if not
          */
         static function tokenValid($token_name, array $data_array = null) {
            if(!\Alo::$session) {
               php_warning('Session handler not initialised or not assigned to \\Alo::$session. FALSE will be returned. ');

               return false;
            } else {
               if($data_array === null) {
                  $data_array = $_POST;
               }

               $sess_token = \Alo::$session->{$token_name};

               return $sess_token && \get($data_array[$token_name]) && $sess_token == $data_array[$token_name];
            }
         }

         /**
          * Removes a token from session data
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $token_name The token's name
          *
          * @return bool TRUE if the session handler was loaded, false if not
          */
         static function tokenRemove($token_name) {
            if(\Alo::$session) {
               \Alo::$session->delete($token_name);

               return true;
            }

            return false;
         }

         /**
          * Returns an unhashed browser/IP fingerprint
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return string
          */
         static function getFingerprint() {
            return '$%c0hYlc$kn!rZF' . \get($_SERVER['HTTP_USER_AGENT'])
                   . \get($_SERVER['HTTP_DNT']) . '^#J!kCRh&H4CKav'
                   . \get($_SERVER['HTTP_ACCEPT_LANGUAGE']) . 'h0&ThYYxk4YOD!g' . \get($_SERVER['REMOTE_ADDR']);
         }

      }
   }
