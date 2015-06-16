<?php

   namespace Alo\Exception;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * File system-related exceptions
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      class FileSystemException extends AbstractException {

         /**
          * Code when opening the file fails
          *
          * @var int
          */
         const E_FOPEN_FAIL = 101;

         /**
          * Code when the file path is not set
          *
          * @var int
          */
         const E_PATH_NOT_SET = 102;

         /**
          * Code when a file doesn't exist
          *
          * @var int
          */
         const E_FILE_NOT_EXISTS = 103;

         /**
          * Code when the file or directory name is invalid
          *
          * @var int
          */
         const E_NAME_INVALID = 104;

         /**
          * Code when content supplied is invalid
          *
          * @var int
          */
         const E_CONTENT_INVALID = 105;

         /**
          * Code when the path is invalid
          *
          * @var int
          */
         const E_PATH_INVALID = 106;
      }
   }