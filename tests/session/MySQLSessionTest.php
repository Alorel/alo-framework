<?php

    use Alo\Db\MySQL;
    use Alo\Session\MySQLSession;

    ob_start();

    class MySQLSessionTest extends AbstractSessionTest {

        function __construct($name = null, $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->wrapper = &PhuGlobal::$mysql;
            $this->handler = 'Alo\Db\MySQL';
        }

        function definedProvider() {
            return [['ALO_SESSION_CLEANUP'],
                    ['ALO_SESSION_TIMEOUT'],
                    ['ALO_SESSION_COOKIE'],
                    ['ALO_SESSION_FINGERPRINT'],
                    ['ALO_SESSION_MC_PREFIX'],
                    ['ALO_SESSION_TABLE_NAME'],
                    ['ALO_SESSION_SECURE']];
        }

        function testSave() {
            MySQLSession::destroySafely();
            MySQLSession::init(PhuGlobal::$mysql);

            $_SESSION['foo'] = 'bar';
            $id              = session_id();

            session_write_close();

            sleep(1);

            $sql         = 'SELECT `data` FROM `alo_session` WHERE `id`=?';
            $sqlParams   = [$id];
            $sessFetched = PhuGlobal::$mysql->prepQuery($sql, $sqlParams, [mySQL::V_CACHE => false]);

            $this->assertNotEmpty($sessFetched, _unit_dump(['sql'     => $sql,
                                                            'params'  => $sqlParams,
                                                            'fetched' => $sessFetched,
                                                            'all'     => PhuGlobal::$mysql->prepQuery('SELECT * FROM `alo_session`')]));

            $sessFetched = $sessFetched[0]['data'];

            $this->assertTrue(stripos($sessFetched, 'foo') !== false, '"foo" not found in session data');
            $this->assertTrue(stripos($sessFetched, 'bar') !== false, '"bar" not found in session data');
            ob_flush();
        }
    }
