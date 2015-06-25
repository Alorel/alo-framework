<?php

    use Alo\Session\AbstractSession;
    use Alo\Db\MySQL;
    use Alo\Cache\MemcachedWrapper;
    use Alo\Cache\RedisWrapper;

    ob_start();

    class AbstractSessionTest extends \PHPUnit_Framework_TestCase {

        /** @var string */
        protected $handler;

        /** @var MySQL|MemcachedWrapper|RedisWrapper */
        protected $wrapper;

        /** @var string cache prefix to use */
        protected $prefix;

        /**
         * @dataProvider definedProvider
         */
        function testDefined($key) {
            $this->assertTrue(defined($key), $key . ' wasn\'t defined');
        }

        function testSave() {
            AbstractSession::destroySafely();
            call_user_func($this->handler . '::init', $this->wrapper);

            $_SESSION['foo'] = 'bar';
            $id              = session_id();
            session_write_close();

            sleep(1);

            $sessFetched = $this->wrapper->get($this->prefix . $id);

            $this->assertNotEmpty($sessFetched, _unit_dump(['id'           => $id,
                                                            'fetched'      => $sessFetched,
                                                            'all'          => $this->wrapper->getAll(),
                                                            'is_available' => $this->wrapper->isAvailable()]));

            $this->assertTrue(stripos($sessFetched, 'foo') !== false, '"foo" not found in session data');
            $this->assertTrue(stripos($sessFetched, 'bar') !== false, '"bar" not found in session data');
        }
    }
