<?php

   namespace Alo\Session;

   use Alo\Cache\RedisWrapper;
   use PhuGlobal;

   class RedisSessionTest extends \PHPUnit_Framework_TestCase {

      function __construct($name = null, $data = [], $dataName = '') {
         parent::__construct($name, $data, $dataName);
         RedisSession::destroySafely();
         RedisSession::init(PhuGlobal::$redisWrapper);
      }

      /**
       * @dataProvider definedProvider
       */
      function testDefined($key) {
         $this->assertTrue(defined($key), $key . ' wasn\'t defined');
         ob_flush();
      }

      function definedProvider() {
         return [
            ['ALO_SESSION_CLEANUP'],
            ['ALO_SESSION_TIMEOUT'],
            ['ALO_SESSION_COOKIE'],
            ['ALO_SESSION_FINGERPRINT'],
            ['ALO_SESSION_REDIS_PREFIX'],
            ['ALO_SESSION_TABLE_NAME']
         ];
      }

      function testSave() {
         $_SESSION['foo'] = 'bar';

         sleep(2);

         $id          = session_id();
         $sessFetched = PhuGlobal::$redisWrapper->get(ALO_SESSION_REDIS_PREFIX . $id);

         $this->assertNotEmpty($sessFetched,
                               _unit_dump([
                                             'id'           => $id,
                                             'fetched'      => $sessFetched,
                                             'all'          => PhuGlobal::$redisWrapper->getAll(),
                                             'is_available' => RedisWrapper::isAvailable()
                                          ]));

         $this->assertTrue(stripos($sessFetched, 'foo') !== false, '"foo" not found in session data');
         $this->assertTrue(stripos($sessFetched, 'bar') !== false, '"bar" not found in session data');
         ob_flush();
      }
   }
