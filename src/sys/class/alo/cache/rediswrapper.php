<?php

    namespace Alo\Cache;

    use Redis;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        \Alo::loadConfig('redis');

        /**
         * A wrapper for PHP's Redis extension.
         *
         * @author  Art <a.molcanovas@gmail.com>
         * @package Cache
         */
        class RedisWrapper extends AbstractCache {

            /**
             * The memcached instance
             *
             * @var Redis
             */
            protected $client;

            /**
             * Instantiates the class
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param boolean $initDefaultServer Whether to add a server on construct
             */
            function __construct($initDefaultServer = true) {
                $this->client = new Redis();
                if ($initDefaultServer) {
                    $this->addServer();
                }
                parent::__construct();

                \Log::debug('RedisWrapper instantiated.');
            }

            /**
             * Adds a server to the pool
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $ip     The server IP
             * @param int    $port   The server port
             * @param int    $weight The server's weight, ie how likely it is to be used. Currently unused by Redis.
             *
             * @return boolean
             */
            function addServer($ip = ALO_REDIS_IP, $port = ALO_REDIS_PORT, $weight = 1) {
                \Log::debug('Added RedisWrapper server ' . $ip . ':' . $port);

                //Param required as per superclass declaration. Unsetting so it doesn't get flagged
                //as unused
                unset($weight);

                return $this->client->connect($ip, $port);
            }

            /**
             * Deletes a memcache key
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $key The key. Can supply multiple keys as arguments to delete them all.
             *
             * @return boolean
             */
            function delete($key) {
                $this->client->delete(func_get_args());

                //Parameter required as per parent declaration; unsetting to prevent flagging
                //as unused var.
                unset($key);

                return true;
            }

            /**
             * Instantiates the class
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param boolean $initDefaultServer Whether to add a server on construct
             *
             * @return RedisWrapper
             */
            static function redisWrapper($initDefaultServer = true) {
                return new RedisWrapper($initDefaultServer);
            }

            /**
             * Gets a cached value
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $id The value's key
             *
             * @return mixed
             */
            function get($id) {
                $get = $this->client->get($id);
                if (!$get) {
                    return null;
                } else {
                    return unserialize($get);
                }
            }

            /**
             * Checks if Redis is available
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return boolean
             */
            static function isAvailable() {
                return class_exists('\Redis');
            }

            /**
             * Return all cached keys and values
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return array
             */
            function getAll() {
                $keys = $this->client->keys('*');
                $r    = [];
                if ($keys) {
                    foreach ($keys as $k) {
                        $r[$k] = $this->get($k);
                    }
                }

                return $r;
            }

            /**
             * Clears all items from cache
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return boolean
             */
            function purge() {
                return $this->client->flushAll();
            }

            /**
             * Sets a cached key/value pair
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $key    The key identifier
             * @param mixed  $var    The value to set
             * @param int    $expire When to expire the set data. Defaults to 3600s.
             *
             * @return boolean
             */
            function set($key, $var, $expire = 3600) {
                \Log::debug('Set the RedisWrapper key ' . $key);

                return $this->client->setex($key, $expire, serialize($var));
            }

        }
    }
