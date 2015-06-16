<?php

   namespace Alo\Exception;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * SFTP-related exceptions
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      class SFTPException extends FileException {

         /**
          * Code when the authentication process fails
          *
          * @var int
          */
         const E_AUTH = 201;

         /**
          * Code when a file cannot be fetched
          *
          * @var int
          */
         const E_FILE_NOT_FETCHED = 202;

         /**
          * Code when file creation fails
          *
          * @var int
          */
         const E_FILE_CREATE_FAIL = 203;

         /**
          * Code when a local file cannot be read
          *
          * @var int
          */
         const E_LOCAL_FILE_NOT_READ = 204;

         /**
          * Code when initialising the SFTP subsystem fails
          *
          * @var int
          */
         const E_SUBSYSTEM = 205;

         /**
          * Code when connection fails
          *
          * @var int
          */
         const E_CONNECT = 206;

         /**
          * Code when a password is invalid
          *
          * @var int
          */
         const E_PW_INVALID = 207;

         /**
          * Code when the URL is invalid
          *
          * @var int
          */
         const E_URL_INVALID = 208;

         /**
          * Code when the user is invalid
          *
          * @var int
          */
         const E_USER_INVALID = 209;
      }
   }