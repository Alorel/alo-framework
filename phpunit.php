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

   _load_classes(__DIR__ . DIRECTORY_SEPARATOR . 'src');

   include __DIR__ . '/vendor/autoload.php';
