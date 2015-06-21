<?php

    namespace Alo\Session;

    use Alo;
    use Alo\Statics\Security;
    use SessionHandlerInterface;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        Alo::loadConfig('session');

        /**
         * The session interface
         *
         * @author  Art <a.molcanovas@gmail.com>
         * @package Session
         */
        abstract class AbstractSession implements SessionHandlerInterface {

            /**
             * Instantiates the class
             *
             * @author Art <a.molcanovas@gmail.com>
             */
            function __construct() {
                $this->setID();
            }

            /**
             * Performs the internal steps of initialising a session
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param Alo\Db\MySQL|Alo\Cache\AbstractCache $dependcyObject Session handlers have a dependency, e.g. a MySQL
             *                                                             instance for MySQLSession, a RedisWrapper instance
             *                                                             for RedisSession etc. You can provide an object
             *                                                             reference containing such an instance here,
             *                                                             otherwise Alo::$db/Alo::$cache will be used.
             * @param string                               $handler        If you want to test a session with a different
             *                                                             handler you can overwrite it here by passing a
             *                                                             class name
             */
            protected static function initSession(&$dependcyObject = null, $handler = ALO_SESSION_HANDLER) {
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_set_cookie_params(ALO_SESSION_TIMEOUT, null, null, ALO_SESSION_SECURE, true);
                    session_name(ALO_SESSION_COOKIE);

                    /** @var Alo\Session\AbstractSession $handler */
                    $handler = new $handler($dependcyObject);

                    session_set_save_handler($handler, true);
                    session_start();
                    $handler->identityCheck();
                } else {
                    phpWarning('A session has already been started');
                }
            }

            /**
             * Only calls session_destroy() if a session is active
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return bool
             */
            static function destroySafely() {
                if (self::isActive()) {
                    session_destroy();

                    return true;
                } else {
                    return false;
                }
            }

            /**
             * Checks whether a session is currently active
             * @author Art <a.molcanovas@gmail.com>
             * @return bool
             */
            static function isActive() {
                return session_status() === PHP_SESSION_ACTIVE;
            }

            /**
             * Closes the session
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return bool
             */
            function close() {
                return true;
            }

            /**
             * Cleans old sessions
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param int $maxlifetime Sessions that have not updated for the last maxlifetime seconds will be removed.
             *
             * @return bool
             */
            function gc($maxlifetime) {
                unset($maxlifetime);

                return true;
            }

            /**
             * Initialize session
             *
             * @author Art <a.molcanovas@gmail.com>
             * @link   http://php.net/manual/en/sessionhandlerinterface.open.php
             *
             * @param string $savePath  Unused, but required for the interface
             * @param string $sessionID Unused, but required for the interface
             *
             * @return bool
             */
            function open($savePath, $sessionID) {
                unset($savePath, $sessionID);

                return true;
            }

            /**
             * Checks if the session hasn't been hijacked
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return boolean TRUE if the check has passed, FALSE if not and the
             *         session has been terminated.
             */
            function identityCheck() {
                $token = self::getToken();
                if (!get($_SESSION[ALO_SESSION_FINGERPRINT])) {
                    $_SESSION[ALO_SESSION_FINGERPRINT] = $token;
                } elseif ($token !== $_SESSION[ALO_SESSION_FINGERPRINT]) {
                    \Log::debug('Session identity check failed');
                    $this->destroy(session_id());

                    return false;
                }
                \Log::debug('Session identity check passed');

                return true;
            }

            /**
             * Destroys a session
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $sessionID The ID to destroy
             *
             * @return bool
             */
            function destroy($sessionID) {
                unset($sessionID);
                setcookie(ALO_SESSION_COOKIE, '', time() - 3, null, null, ALO_SESSION_SECURE, true);

                return true;
            }

            /**
             * Generates a session token
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return string
             */
            protected static function getToken() {
                return md5('sЕss' . Security::getFingerprint() . 'ия');
            }

            /**
             * Sets the session ID variable & the cookie
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return AbstractSession
             */
            protected function setID() {
                $c = get($_COOKIE[ALO_SESSION_COOKIE]);

                if ($c && strlen($c) == 128) {
                    session_id($c);
                } else {
                    session_id(Security::getUniqid('sha512', 'session'));
                }

                \Log::debug('Session ID set to ' . session_id());

                return $this;
            }
        }
    }
