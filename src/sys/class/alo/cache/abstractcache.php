<?php

    namespace Alo\Cache;

    use Alo;
    use Countable;
    use ArrayAccess;
    use IteratorAggregate;
    use ArrayIterator;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {
        /**
         * The abstract cache class
         *
         * @author  Art <a.molcanovas@gmail.com>
         * @package Cache
         */
        abstract class AbstractCache implements Countable, ArrayAccess, IteratorAggregate {

            /**
             * Static reference to the last instance of the class
             *
             * @var AbstractCache
             */
            static $this;

            /**
             * Classes to check in "isAvailable()"
             *
             * @var array
             */
            private static $classes = ['Memcache', 'Memcached', 'Redis'];

            /**
             * The abstract client
             *
             * @var \Redis|\Memcache|\Memcached
             */
            protected $client;

            /**
             * Instantiates the class
             *
             * @author Art <a.molcanovas@gmail.com>
             */
            function __construct() {
                if (!Alo::$cache) {
                    Alo::$cache = &$this;
                }

                self::$this = &$this;
            }

            /**
             * Returns an iterator for a "foreach" loop
             * @author Art <a.molcanovas@gmail.com>
             * @return ArrayIterator
             */
            function getIterator() {
                $all = $this->getAll();

                return new ArrayIterator(is_array($all) ? $all : []);
            }

            /**
             * Sets a value
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $key   The key to set
             * @param mixed  $value The value to set
             */
            function offsetSet($key, $value) {
                if ($key === null) {
                    $key = microtime(true);
                }

                $this->set($key, $value);
            }

            /**
             * Checks if a key exists
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $key The key to look for
             *
             * @return bool
             */
            function offsetExists($key) {
                return $this->get($key) !== null;
            }

            /**
             * Deletes a key
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $key The key
             */
            function offsetUnset($key) {
                $this->delete($key);
            }

            /**
             * Returns the number of cached items
             * @author Art <a.molcanovas@gmail.com>
             * @return int
             */
            function count() {
                $ga = $this->getAll();

                return count($ga);
            }

            /**
             * Checks if a caching extension is available
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return boolean
             */
            static function isAvailable() {
                foreach (self::$classes as $class) {
                    if (class_exists('\\' . $class)) {
                        return true;
                    }
                }

                return false;
            }

            /**
             * Calls a method of the caching client
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $method The method
             * @param array  $args   Method args
             *
             * @return mixed Whatever the method returns
             */
            function __call($method, $args) {
                return call_user_func_array([$this->client, $method], $args);
            }

            /**
             * Key getter
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $key The key
             *
             * @return mixed
             */
            function __get($key) {
                return $this->get($key);
            }

            /**
             * Sets a value with its default expiration time
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $key The key
             * @param mixed  $val The value
             *
             * @return bool
             */
            function __set($key, $val) {
                return $this->set($key, $val);
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
            abstract function get($id);

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
            abstract function set($key, $var, $expire = 3600);

            /**
             * Checks if a key is set in cache
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $key The key
             *
             * @return bool
             */
            function __isset($key) {
                return $this->offsetExists($key);
            }

            /**
             * Removes a key from cache
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $key The key
             */
            function __unset($key) {
                $this->delete($key);
            }

            /**
             * Gets a cached item
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $key The key
             *
             * @return mixed
             */
            function offsetGet($key) {
                return $this->get($key);
            }

            /**
             * Deletes a memcache key
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $key The key
             *
             * @return boolean
             */
            abstract function delete($key);

            /**
             * Clears all items from cache
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return boolean
             */
            abstract function purge();

            /**
             * Adds a server to the pool
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $ip     The server IP
             * @param int    $port   The server port
             * @param int    $weight The server's weight, ie how likely it is to be used
             *
             * @return boolean
             */
            abstract function addServer($ip, $port, $weight);

            /**
             * Deletes all cached entries with the supplied prefix
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $prefix The prefix
             *
             * @return AbstractCache
             */
            function deleteWithPrefix($prefix) {
                $length  = strlen($prefix);
                $entries = array_keys($this->getAll());

                \Log::debug('Deleting all cache entries with prefix ' . $prefix);
                foreach ($entries as $key) {
                    if (substr($key, 0, $length) == $prefix) {
                        $this->delete($key);
                    }
                }

                return $this;
            }

            /**
             * Return all cached keys and values
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return array
             */
            abstract function getAll();

            /**
             * Deletes all cached entries with the supplied suffix
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $suffix The suffix
             *
             * @return AbstractCache
             */
            function deleteWithSuffix($suffix) {
                $length  = strlen($suffix) * -1;
                $entries = array_keys($this->getAll());

                \Log::debug('Deleting all cache entries with suffix ' . $suffix);
                foreach ($entries as $key) {
                    if (substr($key, $length) == $suffix) {
                        $this->delete($key);
                    }
                }

                return $this;
            }

        }
    }
