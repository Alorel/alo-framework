<?php

    namespace Alo\Session;

    use Alo\Cache\RedisWrapper;
    use PhuGlobal;

    ob_start();

    class RedisSessionTest extends \PHPUnit_Framework_TestCase {

        /**
         * @dataProvider definedProvider
         */
        function testDefined($key) {
            $this->assertTrue(defined($key), $key . ' wasn\'t defined');
            ob_flush();
        }

        function definedProvider() {
            return [['ALO_SESSION_CLEANUP'],
                    ['ALO_SESSION_TIMEOUT'],
                    ['ALO_SESSION_COOKIE'],
                    ['ALO_SESSION_FINGERPRINT'],
                    ['ALO_SESSION_REDIS_PREFIX'],
                    ['ALO_SESSION_TABLE_NAME']];
        }

        function testSave() {
            RedisSession::destroySafely();
            RedisSession::init(PhuGlobal::$redisWrapper);

            $_SESSION['foo'] = 'bar';
            $id              = session_id();

            session_write_close();
            sleep(1);

            $sessFetched = PhuGlobal::$redisWrapper->get(ALO_SESSION_REDIS_PREFIX . $id);

            $this->assertNotEmpty($sessFetched, _unit_dump(['id'           => $id,
                                                            'fetched'      => $sessFetched,
                                                            'all'          => PhuGlobal::$redisWrapper->getAll(),
                                                            'is_available' => RedisWrapper::isAvailable()]));

            $this->assertTrue(stripos($sessFetched, 'foo') !== false, '"foo" not found in session data');
            $this->assertTrue(stripos($sessFetched, 'bar') !== false, '"bar" not found in session data');
            ob_flush();
        }
    }
