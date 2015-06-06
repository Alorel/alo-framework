<?php

   namespace Alo\Decorator\TwitterBootstrap;

   if(!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   /**
    * Navbar drawer
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   class Navbar {

      /**
       * The loaded navbar
       *
       * @var array
       */
      protected $raw = [];

      /**
       * Brand name
       *
       * @var string
       */
      protected $brand;

      /**
       * Active tab names
       *
       * @var array
       */
      protected $active = [];

      /**
       * Navbar ID
       *
       * @var string
       */
      protected $id;

      /**
       * Loads the decorator
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param array  $active If supplied, will automatically load the active menu items
       * @param string $brand  If supplied, will automatically load the brand name
       * @param array  $navbar If supplied, the navbar array will be automatically loaded
       * @param string $id     If supplied, the navbar ID will be automatically set
       *
       * @see    Navbar::raw()
       */
      function __construct(array $active = null, $brand = null, array $navbar = null, $id = null) {
         if($navbar) {
            $this->raw($navbar);
         }

         if($brand) {
            $this->brand($brand);
         }

         if($active) {
            $this->active($active);
         }

         if($id) {
            $this->id($id);
         }
      }

      /**
       * Gets or sets the navbar array, depending on whether a parameter is passed
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param null|array $set If setting, the navbar array
       *
       * @return bool|array TRUE if the parameter has been set, $this->raw otherwise
       */
      function raw($set = null) {
         if(is_array($set) && !empty($set)) {
            $this->raw = $set;

            return true;
         } else {
            return $this->raw;
         }
      }

      /**
       * Gets or sets the brand name, depending on whether a parameter is passed
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param null|string $set If setting, the brand name
       *
       * @return bool|array TRUE if the parameter has been set, $this->brand otherwise
       */
      function brand($set = null) {
         if($set && is_scalar($set)) {
            $this->brand = $set;

            return true;
         } else {
            return $this->brand;
         }
      }

      /**
       * Gets or sets the active items array, depending on whether a parameter is passed
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param null|array $set If setting, the active items array
       *
       * @return bool|array TRUE if the parameter has been set, $this->raw otherwise
       */
      function active($set = null) {
         if(is_array($set)) {
            $this->active = $set;

            return true;
         } else {
            return $this->active;
         }
      }

      /**
       * Gets or sets the navbar ID, depending on whether a parameter is passed
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param null|string $set If setting, the navbar ID
       *
       * @return bool|string TRUE if the parameter has been set, $this->raw otherwise
       */
      function id($set = null) {
         if($set && is_scalar($set)) {
            $this->id = $set;

            return true;
         } else {
            return $this->id;
         }
      }

      /**
       * Loads the decorator
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param array  $active If supplied, will automatically load the active menu items
       * @param string $brand  If supplied, will automatically load the brand name
       * @param array  $navbar If supplied, the navbar array will be automatically loaded
       * @param string $id     If supplied, the navbar ID will be automatically set
       *
       * @return Navbar
       * @see    Navbar::raw()
       */
      static function Navbar(array $active = null, $brand = null, array $navbar = null, $id = null) {
         return new Navbar($active, $brand, $navbar, $id);
      }

      /**
       * Accessor for decorate()
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       * @see    Navbar::decorate()
       */
      function __toString() {
         return $this->decorate();
      }

      /**
       * Generates the navbar HTML
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string HTML code
       */
      function decorate() {
         if(!$this->id) {
            $this->id = 'nav' . md5(time() . json_encode([$this->raw, $this->active]));
         }

         $r = '<nav class = "navbar navbar-default img-rounded">'
              . '<div class = "container-fluid">'
              . '<div class = "navbar-header">'
              . '<button type = "button" class = "navbar-toggle collapsed" data-toggle = "collapse" data-target="#' .
              $this->id . '">'
              . '<span class = "sr-only">Toggle navigation</span>'
              . '<div style="width: 20px">'
              . '<span class = "icon-droid-menu"></span>'
              . '<span class = "icon-droid-menu"></span>'
              . '<span class = "icon-droid-menu"></span>'
              . '</div>'
              . '</button>'
              . '<a class = "navbar-brand" href = "/">' . $this->brand . '</a>'
              . '</div>'
              . '<div class = "collapse navbar-collapse" id="' . $this->id . '">'
              . '<ul class = "nav navbar-nav">';

         $this->doDecorate($this->raw, $r);

         $r .= '</div></div></nav>';

         return $r;
      }

      /**
       * Generates the list items
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param array  $array The currently traversed array
       * @param string $r     Reference to the return HTML variable
       */
      protected function doDecorate(array $array, &$r) {
         foreach($array as $text => $spec) {
            $active = in_array($text, $this->active);
            if(get($spec['children'])) {
               $r .=
                  '<li class="dropdown' . ($active ? ' active' : '') . '">'
                  . '<a href="'
                  . $spec['url']
                  . '" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'
                  . $text
                  . '<span class="caret"></span>'
                  . '</a>'
                  . '<ul class="dropdown-menu">';

               $this->doDecorate($spec['children'], $r);

               $r .= '</ul>';

            } else {
               $r .= '<li' . ($active ? ' class="active"' : '') . '><a href="' . $spec['url'] . '">' . $text . '</a>';
            }
            $r .= '</li>';
         }
      }
   }