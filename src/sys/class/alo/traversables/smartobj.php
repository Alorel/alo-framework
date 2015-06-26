<?php

    namespace Alo\Traversables;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * Some smarter array functions. This is a very work-in-progress class.
         * @author Art <a.molcanovas@gmail.com>
         */
        class SmartObj extends ArrayObj {

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
             * Internal handler for regex deletion
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $regex       The regular expression
             * @param bool   $recursive   Whther to recurse into child arrays
             * @param bool   $checkingKey true if checking key regex, false if checking value regex
             * @param bool   $inverse     If set to true the method will delete items that do NOT match the regex
             * @param array  $currArray   Reference to the currently checked array
             */
            protected function deleteRegexInternal($regex, $recursive, $checkingKey, $inverse, &$currArray) {
                foreach ($currArray as $k => &$v) {
                    $checking = $checkingKey ? $k : $v;

                    if (is_scalar($checking) &&
                        ($inverse ? !preg_match($regex, $checking) : preg_match($regex, $checking))
                    ) {
                        unset($currArray[$k]);
                    } elseif ($recursive && is_array($v)) {
                        $this->deleteRegexInternal($regex, $recursive, $checkingKey, $inverse, $v);
                    }
                }
            }

            /**
             * Deletes all elements from an array where the value matches the supplied regular expression
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $regex     The regular expression
             * @param bool   $recursive Whether to recurse into child arrays
             * @param bool   $inverse   If set to true the method will delete items that do NOT match the regex
             *
             * @return SmartObj
             */
            function deleteWithValueRegex($regex, $recursive = true, $inverse = false) {
                $this->deleteRegexInternal($regex, $recursive, false, $inverse, $this->data);

                return $this;
            }

            /**
             * Deletes all elements from an array where the key matches the supplied regular expression
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $regex     The regular expression
             * @param bool   $recursive Whether to recurse into child arrays
             * @param bool   $inverse   If set to true the method will delete items that do NOT match the regex
             *
             * @return SmartObj
             */
            function deleteWithKeyRegex($regex, $recursive = true, $inverse = false) {
                $this->deleteRegexInternal($regex, $recursive, true, $inverse, $this->data);

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
        }
    }
