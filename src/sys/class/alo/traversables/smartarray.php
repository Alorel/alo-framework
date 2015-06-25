<?php

    namespace Alo\Traversables;

    use IteratorAggregate;
    use ArrayIterator;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * Some smarter array functions
         * @author Art <a.molcanovas@gmail.com>
         */
        class SmartArray implements IteratorAggregate {

            /**
             * The array we're working with
             * @var array
             */
            protected $array;

            /**
             * Initialises our array
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $initial The initial array or Traversable to set
             */
            function __construct(array $initial = []) {
                $this->array = $initial;
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
                return get($this->array[$k]);
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
                $this->array[$k] = $v;
            }

            /**
             * Returns the data set
             *
             * @author Art <a.molcanovas@gmail.com>
             * @return array
             */
            function toArray() {
                return $this->array;
            }

            /**
             * Removes all the duplicate values from an array
             * @author Art <a.molcanovas@gmail.com>
             *
             * @return SmartArray
             */
            function uniqueRecursive() {
                $this->uniqueRecursiveInternal($this->array);

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
            public function getIterator() {
                return new ArrayIterator($this->array);
            }
        }
    }
