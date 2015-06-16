<?php

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * Hosts as well as backup hosts to use, separated by semicolon
       *
       * @var string
       */
      define('ALO_EMAIL_HOSTS', 'smtp1.example.com;smtp2.example.com');

      /**
       * Whether to use SMTP by default
       *
       * @var boolean
       */
      define('ALO_EMAIL_USE_SMTP', true);

      /**
       * Whether to use authentication
       *
       * @var boolean
       */
      define('ALO_EMAIL_AUTH', true);

      /**
       * Default email error message language. See files at /sys/external/email/language
       *
       * @var string
       */
      define('ALO_EMAIL_ERR_LANG', 'en');

      /**
       * Authentication username
       *
       * @var string
       */
      define('ALO_EMAIL_USERNAME', 'you');

      /**
       * Authentication password
       *
       * @var string
       */
      define('ALO_EMAIL_PASSWORD', 'top_secret');

      /**
       * Security protocol to use
       *
       * @var string
       */
      define('ALO_EMAIL_SECURE', 'tls');

      /**
       * Email port
       *
       * @var int
       */
      define('ALO_EMAIL_PORT', 587);

      /**
       * Address messages will come from by default
       *
       * @var string
       */
      define('ALO_EMAIL_FROM_DEFAULT_ADDR', 'foo@bar.com');

      /**
       * Sender name by default
       *
       * @var string
       */
      define('ALO_EMAIL_FROM_DEFAULT_NAME', 'My Fancy Name');

      /**
       * Whether to enable HTML emails by defualt
       *
       * @var boolean
       */
      define('ALO_EMAIL_HTML_ENABLED', true);

      /**
       * Default subject to set
       *
       * @var string
       */
      define('ALO_EMAIL_SUBJECT_DEFAULT', 'My subject');
   }
