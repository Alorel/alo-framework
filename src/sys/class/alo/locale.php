<?php

   namespace Alo;

   use Alo;
   use Alo\Db\AbstractDb as DB;
   use Alo\Exception\LibraryException;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      Alo::loadConfig('locale');

      /**
       * Locale handler
       *
       * @author     Arturas Molcanovas <a.molcanovas@gmail.com>
       * @todo       Write tests
       */
      class Locale {

         /**
          * Static reference to the last instance of this class
          *
          * @var Locale
          */
         static $this;
         /**
          * prepQuery settings
          *
          * @var array
          */
         protected static $query_settings = [
            DB::V_CACHE => true,
            DB::V_TIME  => ALO_LOCALE_CACHE_TIME
         ];
         /**
          * Reference to the database connection
          *
          * @var DB
          */
         protected $db;
         /**
          * Fetched items
          *
          * @var array
          */
         protected $fetched = [];
         /**
          * Raw fetched array
          *
          * @var array
          */
         protected $raw;
         /**
          * Whether the initial fetch has been done
          *
          * @var bool
          */
         protected $first_fetch_done = false;

         /**
          * Instantiates the Locale handler
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param DB $db If not using Alo::$db you can supply the reference to the database connection here
          *
          * @throws LibraryException If the above reference is not supplied and Alo::$db is not instantiated
          */
         function __construct(DB &$db = null) {
            if($db) {
               $this->db = &$db;
            } elseif(Alo::$db) {
               $this->db = &Alo::$db;
            } else {
               throw new LibraryException('Alo::$db does not have a database connection assigned.', LibraryException::E_REQUIRED_LIB_NOT_FOUND);
            }

            self::$this = &$this;
         }

         /**
          * Instantiates the Locale handler
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param DB $db If not using Alo::$db you can supply the reference to the database connection here
          *
          * @throws LibraryException If the above reference is not supplied and Alo::$db is not instantiated
          * @return Locale
          */
         static function Locale(DB &$db = null) {
            return new Locale($db);
         }

         /**
          * Performs a locale fetch
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param array  $pages Pages to fetch.
          * @param string $primary_locale
          * @param null   $seconday_locale
          *
          * @return Locale
          */
         function fetch(array $pages = null, $primary_locale = ALO_LOCALE_DEFAULT, $seconday_locale = null) {
            $arr_global = ['global'];

            if(!$this->first_fetch_done) {
               $pages                  = is_array($pages) ? array_merge($arr_global, $pages) : $arr_global;
               $this->first_fetch_done = true;
            }

            if($pages !== null) {
               if(!is_array($pages)) {
                  $pages = $arr_global;
               }

               if($seconday_locale) {
                  $this->fetchTwo($pages, $primary_locale, $seconday_locale);
               } else {
                  $this->fetchOne($pages, $primary_locale);
               }

               $this->formatRaw();
            }

            return $this;
         }

         /**
          * Fetches a raw dual-locale resultset (primary being a fallback)
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param array  $pages            Pages to fetch
          * @param string $primary_locale   The primary locale
          * @param string $secondary_locale The secondary locale
          */
         protected function fetchTwo(array $pages, $primary_locale, $secondary_locale) {
            $params = [
               ':first'  => $primary_locale,
               ':second' => $secondary_locale
            ];

            $sql = 'SELECT DISTINCT `default`.`key`,'
                   . 'IFNULL(`loc`.`value`,`default`.`value`) AS `value` '
                   . 'FROM `alo_locale` `default` '
                   . 'LEFT JOIN `alo_locale` `loc` '
                   . 'ON `loc`.`lang`=:second '
                   . 'AND `loc`.`page` = `default`.`page` '
                   . 'AND `loc`.`key`=`default`.`key` '
                   . 'WHERE `default`.`lang`=:first';

            if(!ALO_LOCALE_FETCH_ALL) {
               $sql .= ' AND `default`.`page` IN (';
               foreach($pages as $i => $p) {
                  $sql .= ':p' . $i . ',';
                  $params[':p' . $i] = $p;
               }

               $sql = rtrim($sql, ',') . ')';
            }

            $sql .= ' ORDER BY NULL';

            $this->raw = $this->db->prepQuery($sql, $params, self::$query_settings);
         }

         /**
          * Fetches a raw single-locale resultset
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param array    $pages  pages to fetch
          * @param   string $locale Locale to fetch
          */
         protected function fetchOne(array $pages, $locale) {
            $params = [
               ':first' => $locale
            ];

            $sql = 'SELECT `key`,'
                   . '`value` '
                   . 'FROM `alo_locale` '
                   . 'WHERE `lang`=:first';

            if(!ALO_LOCALE_FETCH_ALL) {
               $sql .= ' AND `page` IN (';
               foreach($pages as $i => $p) {
                  $sql .= ':p' . $i . ',';
                  $params[':p' . $i] = $p;
               }

               $sql = rtrim($sql, ',') . ')';
            }

            $sql .= ' ORDER BY NULL';

            $this->raw = $this->db->prepQuery($sql, $params, self::$query_settings);
         }

         /**
          * Formats raw data
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return Locale
          */
         protected function formatRaw() {
            if($this->raw) {
               foreach($this->raw as $row) {
                  $this->fetched[$row['key']] = $row['value'];
               }
            }

            return $this;
         }

         /**
          * Returns a localised string
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $key String key
          *
          * @return string|null
          */
         function __get($key) {
            return get($this->fetched[$key]);
         }
      }
   }