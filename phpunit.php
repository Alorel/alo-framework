<?php

   include_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'index.php';

   function _load_classes($dirName) {
      $di = new DirectoryIterator($dirName);
      foreach ($di as $file) {
         if ($file->isDir() && !$file->isLink() && !$file->isDot()) {
            // recurse into directories other than a few special ones
            _load_classes($file->getPathname());
         } elseif (substr($file->getFilename(), -4) === '.php') {
            // save the class name / path of a .php file found
            include_once $file->getPathname();
         }
      }
   }

   function _unit_dump(array $data) {
      ob_start();
      var_dump($data);

      return ob_get_clean();
   }

   // ========== Autoload classes ==========
   _load_classes(DIR_SYS . 'core');
   _load_classes(DIR_SYS . 'class');
   _load_classes(DIR_APP . 'class');
   _load_classes(DIR_APP . 'interface');
   _load_classes(DIR_APP . 'traits');

   include __DIR__ . '/vendor/autoload.php';
