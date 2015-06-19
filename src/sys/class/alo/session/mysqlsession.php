<?php

   namespace Alo\Session;

   use Alo;
   use Alo\Db\MySQL;
   use Alo\Exception\LibraryException as Libex;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } else {

      /**
       * MySQL-based session handler
       *
*@author Art <a.molcanovas@gmail.com>
       */
      class MySQLSession extends AbstractSession {

         /**
          * Database instance
          *
          * @var MySQL
          */
         protected $db;

         /**
          * Constructor
          *
          * @author Art <a.molcanovas@gmail.com>
          * @throws Libex When $cacheInstance is not passed and Alo::$cache does not contain a MemcachedWrapper instance
          *
          * @param MySQL $instance If a parameter is passed here its instance will be used instead of Alo::$db
          */
         function __construct(MySQL &$instance = null) {
            if($instance) {
               $this->db = &$instance;
            } elseif(Alo::$db && Alo::$db instanceof MySQL) {
               $this->db = &Alo::$db;
            } else {
               throw new Libex('MySQL instance not found', Libex::E_REQUIRED_LIB_NOT_FOUND);
            }

            parent::__construct();
         }

         /**
          * Destroys the session
          *
*@author Art <a.molcanovas@gmail.com>
          *
*@param string $sessionID The session ID
          *
          * @return array|bool
          */
         public function destroy($sessionID) {
            return $this->db->prepQuery('DELETE FROM `' . ALO_SESSION_TABLE_NAME . '` WHERE `id`=?',[$sessionID]);
         }

         /**
          * Read ssession data
          *
          * @author Art <a.molcanovas@gmail.com>
          * @link http://php.net/manual/en/sessionhandlerinterface.read.php
          *
          * @param string $sessionID The session id to read data for.
          *
          * @return string
          */
         public function read($sessionID) {
            $data = $this->db->prepQuery('SELECT `data` FROM `' . ALO_SESSION_TABLE_NAME . '` WHERE `id`=?',[$sessionID]);

            return $data ? $data[0]['data'] : '';
         }

         /**
          * Write session data
          *
          * @author Art <a.molcanovas@gmail.com>
          * @link http://php.net/manual/en/sessionhandlerinterface.write.php
          *
          * @param string $sessionID    The session id.
          * @param string $sessionData  The encoded session data. This data is the
          *                             result of the PHP internally encoding
          *                             the $_SESSION superglobal to a serialized
          *                             string and passing it as this parameter.
          *                             Please note sessions use an alternative serialization method.
          *
          * @return bool
          */
         public function write($sessionID, $sessionData) {
            return $this->db->prepQuery('INSERT INTO `' . ALO_SESSION_TABLE_NAME . '`('
                                        .'`id`,'
                                        .'`data`,'
                                        .'`access`) VALUES(:id,:data,CURRENT_TIMESTAMP) '
                                        .'ON DUPLICATE KEY UPDATE '
                                        .'`data`=VALUES(`data`),'
                                        .'`access`=CURRENT_TIMESTAMP',[
                                           ':id' => $sessionID,
                                           ':data' => $sessionData
                                        ]);
         }
      }
   }
