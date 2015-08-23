<?php

    namespace Alo;

    use Alo;
    use Alo\Db\AbstractDb as DB;
    use Alo\Exception\LibraryException;
    use Alo\Traversables\ArrayObj;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        Alo::loadConfig('locale');

        /**
         * Locale handler
         *
         * @author     Arturas Molcanovas <a.molcanovas@gmail.com>
         */
        class Locale extends ArrayObj {

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
            protected static $querySettings = [DB::V_CACHE => true,
                                               DB::V_TIME  => ALO_LOCALE_CACHE_TIME];

            /**
             * Reference to the database connection
             *
             * @var DB
             */
            protected $db;

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
            protected $firstFetchDone = false;

            /**
             * The default global key
             * @var array
             */
            protected static $arrGlobal = ['global'];

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
                if ($db) {
                    $this->db = &$db;
                } elseif (Alo::$db && Alo::$db instanceof DB) {
                    $this->db = &Alo::$db;
                } else {
                    throw new LibraryException('Alo::$db does not have a database connection assigned.',
                                               LibraryException::E_REQUIRED_LIB_NOT_FOUND);
                }

                parent::__construct();
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
            static function locale(DB &$db = null) {
                return new Locale($db);
            }

            /**
             * Performs a locale fetch
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array  $pages           Pages to fetch.
             * @param string $primaryLocale   The main locale - will be used as a fallback if a string is unavailable
             *                                for the secondary locale
             * @param string $secondaryLocale If you're fetching for the secondary locale, input it here.
             *
             * @return Locale
             */
            function fetch(array $pages = null, $primaryLocale = null, $secondaryLocale = null) {
                if (!$primaryLocale) {
                    $primaryLocale = ALO_LOCALE_DEFAULT;
                }

                if (!$this->firstFetchDone) {
                    $pages                =
                        is_array($pages) ? array_unique(array_merge(self::$arrGlobal, $pages)) : self::$arrGlobal;
                    $this->firstFetchDone = true;
                }

                if ($pages !== null) {
                    if (!is_array($pages)) {
                        $pages = self::$arrGlobal;
                    }

                    if ($secondaryLocale && $secondaryLocale !== $primaryLocale) {
                        $this->fetchTwo($pages, $primaryLocale, $secondaryLocale);
                    } else {
                        $this->fetchOne($pages, $primaryLocale);
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
             * @param array  $pages           Pages to fetch
             * @param string $primaryLocale   The primary locale
             * @param string $secondaryLocale The secondary locale
             */
            protected function fetchTwo(array $pages, $primaryLocale, $secondaryLocale) {
                $params = [':first'  => $primaryLocale,
                           ':second' => $secondaryLocale];

                $sql =
                    'SELECT DISTINCT `default`.`key`,' .
                    'IFNULL(`loc`.`value`,`default`.`value`) AS `value` ' .
                    'FROM `alo_locale` `default` ' .
                    'LEFT JOIN `alo_locale` `loc` ' .
                    'ON `loc`.`lang`=:second ' .
                    'AND `loc`.`page` = `default`.`page` ' .
                    'AND `loc`.`key`=`default`.`key` ' .
                    'WHERE `default`.`lang`=:first';

                if (!ALO_LOCALE_FETCH_ALL) {
                    $sql .= ' AND `default`.`page` IN (';
                    foreach ($pages as $i => $p) {
                        $sql .= ':p' . $i . ',';
                        $params[':p' . $i] = $p;
                    }

                    $sql = rtrim($sql, ',') . ')';
                }

                $sql .= ' ORDER BY NULL';

                $this->raw = $this->db->prepQuery($sql, $params, self::$querySettings);
            }

            /**
             * Fetches a raw single-locale resultset
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array  $pages  pages to fetch
             * @param string $locale Locale to fetch
             */
            protected function fetchOne(array $pages, $locale) {
                $params = [':first' => $locale];

                $sql = 'SELECT `key`,' . '`value` ' . 'FROM `alo_locale` ' . 'WHERE `lang`=:first';

                if (!ALO_LOCALE_FETCH_ALL) {
                    $sql .= ' AND `page` IN (';
                    foreach ($pages as $i => $p) {
                        $sql .= ':p' . $i . ',';
                        $params[':p' . $i] = $p;
                    }

                    $sql = rtrim($sql, ',') . ')';
                }

                $sql .= ' ORDER BY NULL';

                $this->raw = $this->db->prepQuery($sql, $params, self::$querySettings);
            }

            /**
             * Formats raw data
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return Locale
             */
            protected function formatRaw() {
                if ($this->raw) {
                    foreach ($this->raw as $row) {
                        $this->data[$row['key']] = $row['value'];
                    }
                }

                return $this;
            }

            /**
             * Returns the fetched locale array
             * @author Art <a.molcanovas@gmail.com>
             * @return array
             */
            function getAll() {
                return $this->data;
            }
        }
    }
