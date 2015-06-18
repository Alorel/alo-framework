<?php

   use Alo\Db\MySQL;
   use Alo\Locale;

   class LocaleTest extends PHPUnit_Framework_TestCase {

      /**
       * @var Locale
       */
      private $locale;

      /**
       * @var MySQL
       */
      private $db;

      function __construct($name = null, array $data = [], $dataName = '') {
         parent::__construct($name, $data, $dataName);
         $this->db     = new MySQL();
         $this->locale = new Locale($this->db);
      }

      function testTmp() {
         $this->assertTrue(true);
         ob_flush();
      }
   }
