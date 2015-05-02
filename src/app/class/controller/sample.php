<?php

   namespace Controller;

   use Alo\Controller\AbstractController;

   if (!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * The home controller
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   class Sample extends AbstractController {

      /**
       * Default index page
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      function index() {
         $this->loadView('sample', ['foo' => 'bar']);
      }

   }