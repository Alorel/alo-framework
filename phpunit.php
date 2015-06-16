<?php

   ob_start();
   include_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'index.php';

   define('PHPUNIT_ROOT', __DIR__ . DIRECTORY_SEPARATOR);

   define('PHPUNIT_RUNNING', true);

   function _load_classes($dirName) {
      $di = new DirectoryIterator($dirName);
      foreach($di as $file) {
         if($file->isDir() && !$file->isLink() && !$file->isDot()) {
            // recurse into directories other than a few special ones
            _load_classes($file->getPathname());
         } elseif(substr($file->getFilename(), -4) === '.php') {
            // save the class name / path of a .php file found
            include_once $file->getPathname();
         }
      }
   }

   function _unit_dump($data) {
      ob_start();
      var_dump($data);

      return ob_get_clean();
   }

   function phpunit_debug($msg) {
      echo '[DEBUG] ' . $msg . PHP_EOL;
   }

   function phpunit_scalarise($var) {
      if(is_scalar($var)) {
         return $var;
      } else {
         ob_start();
         var_dump($var);

         return ob_get_clean();
      }
   }

   // ========== Autoload classes ==========
   _load_classes(DIR_SYS . 'core');
   _load_classes(DIR_SYS . 'class');
   _load_classes(DIR_APP . 'class');
   _load_classes(DIR_APP . 'interface');
   _load_classes(DIR_APP . 'traits');

   Alo::$router = new \Alo\Controller\Router();
   ob_end_clean();

   include __DIR__ . '/vendor/autoload.php';
