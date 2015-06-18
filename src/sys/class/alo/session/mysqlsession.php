<?php

   namespace Alo\Session;

   use Alo;
   use Alo\Db\MySQL;
   use Alo\Exception\LibraryException as Libex;

   if (!defined('GEN_START')) {
      http_response_code(404);
   } else {


      /**
       * The MySQL-based session handler. ALO_SESSION_CLEANUP is not used here as
       * cleanup is handled by the MySQL event handler
       *
       * @author  Art <a.molcanovas@gmail.com>
       * @package Session
       */
      class MySQLSession extends AbstractSession {

         /**
          * Reference to database instance
          *
          * @var MySQL
          */
         protected $db;

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws Libex When $cacheInstance is not passed and Alo::$cache does not contain a MemcachedWrapper instance
          *
          * @param MySQL $db If a parameter is passed here its instance will be used instead of Alo::$db
          */
         function __construct(MySQL &$db = null) {
            if ($db) {
               $this->db = &$db;
            } elseif (Alo::$db && Alo::$db instanceof MySQL) {
               $this->db = &Alo::$db;
            } else {
               throw new Libex('MySQL instance not found',Libex::E_REQUIRED_LIB_NOT_FOUND);
            }

            parent::__construct();
            \Log::debug('Initialised MySQL session');
         }

         /**
          * Instantiates the class
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param MySQL $db If a parameter is passed here its instance will be used instead of Alo::$db
          * @return MySQLSession
          */
         static function mysqlSession(MySQL &$db = null) {
            return new MySQLSession($db);
         }

         /**
          * Fetches session data
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return MySQLSession
          */
         protected function fetch() {
            $sql = $this->db->prepQuery('SELECT `data` '
                                        . 'FROM `' . ALO_SESSION_TABLE_NAME . '` '
                                        . 'WHERE `id`=? '
                                        . 'LIMIT 1', [$this->id], [
                                           MySQL::V_CACHE => false
                                        ]);

            if (!empty($sql)) {
               $this->data = json_decode($sql[0]['data'], true);
            }

            \Log::debug('Saved session data');

            return $this;
         }

         /**
          * Terminates the session
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return MySQLSession
          */
         function terminate() {
            $this->db->prepQuery('DELETE FROM `' . ALO_SESSION_TABLE_NAME . '` '
                                 . 'WHERE `id`=? '
                                 . 'LIMIT 1', [$this->id], [MySQL::V_CACHE => false]);

            return parent::terminate();
         }

         /**
          * Saves session data
          *
          * @author Art <a.molcanovas@gmail.com>
          * @return MySQLSession
          */
         protected function write() {
            $this->db->prepQuery('REPLACE INTO `'
                                 . ALO_SESSION_TABLE_NAME . '`(`id`,`data`,`access`) VALUES('
                                 . '?,?,CURRENT_TIMESTAMP)', [
                                    $this->id,
                                    json_encode($this->data)
                                 ], [MySQL::V_CACHE => false]);

            \Log::debug('Saved session data');

            return $this;
         }

      }
   }
