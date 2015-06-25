<?php

    namespace Alo\Traversables;

    use IteratorAggregate;
    use ArrayIterator;
    use ArrayAccess;
    use Countable;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * Some smarter array functions. This is a very work-in-progress class.
         * @author Art <a.molcanovas@gmail.com>
         */
        class SmartObj implements IteratorAggregate, ArrayAccess, Countable {

            /**
             * The array we're working with
             * @var array
             */
            protected $data;

            /**
             * Initialises our smart array-based object
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $initial The initial array to set
             */
            function __construct(array $initial = []) {
                $this->data = $initial;
            }

            /**
             * Returns the number of items in the data object
             * @author Art <a.molcanovas@gmail.com>
             * @return int
             */
            function count() {
                return count($this->data);
            }

            /**
             * Initialises our smart array-based object
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $initial The initial array to set
             *
             * @return SmartObj
             */
            static function smartObj(array $initial = []) {
                return new SmartObj($initial);
            }

            /**
             * Returns an array value
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $k The value's key
             *
             * @return mixed
             */
            function __get($k) {
                return get($this->data[$k]);
            }

            /**
             * Sets an array value
             *
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $k The key
             * @param mixed  $v The value
             */
            function __set($k, $v) {
                $this->data[$k] = $v;
            }

            /**
             * Returns the data set
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return array
             */
            function toArray() {
                return $this->data;
            }

            /**
             * Removes all the duplicate values from an array
             * @author Art <a.molcanovas@gmail.com>
             *
             * @return SmartObj
             */
            function uniqueRecursive() {
                $this->uniqueRecursiveInternal($this->data);

                return $this;
            }

            /**
             * Internal handler for uniquefyRecursively
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $curr Reference to the currently processed step
             */
            protected function uniqueRecursiveInternal(array &$curr) {
                foreach ($curr as &$v) {
                    if (is_array($v)) {
                        $v = array_map('unserialize', array_unique(array_map('serialize', $v)));
                        $this->uniqueRecursiveInternal($v);
                    }
                }
            }

            /**
             * Returns the array iterator for our data
             * @author Art <a.molcanovas@gmail.com>
             *
             * @return ArrayIterator
             */
            function getIterator() {
                return new ArrayIterator($this->data);
            }

            /**
             * Checks whether a offset exists
             * @author Art <a.molcanovas@gmail.com>
             * @link   http://php.net/manual/en/arrayaccess.offsetexists.php
             *
             * @param mixed $offset An offset to check for.
             *
             * @return boolean
             */
            function offsetExists($offset) {
                return array_key_exists($offset, $this->data);
            }

            /**
             * Gets an offset
             * @author Art <a.molcanovas@gmail.com>
             * @link   http://php.net/manual/en/arrayaccess.offsetget.php
             *
             * @param mixed $offset The offset to retrieve.
             *
             * @return mixed
             */
            function offsetGet($offset) {
                return get($this->data[$offset]);
            }

            /**
             * Sets an offset
             * @author Art <a.molcanovas@gmail.com>
             * @link   http://php.net/manual/en/arrayaccess.offsetset.php
             *
             * @param mixed $offset The offset to assign the value to.
             * @param mixed $value  The value to set.
             */
            function offsetSet($offset, $value) {
                if ($offset === null) {
                    $this->data[] = $value;
                } else {
                    $this->data[$offset] = $value;
                }
            }

            /**
             * Unsets an offset
             * @author Art <a.molcanovas@gmail.com>
             * @link   http://php.net/manual/en/arrayaccess.offsetunset.php
             *
             * @param mixed $offset The offset to unset.
             */
            function offsetUnset($offset) {
                unset($this->data[$offset]);
            }
        }
    }
