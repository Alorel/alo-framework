<?php

    namespace Alo\Db;

    use Alo\Traversables\ArrayObj;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * SQL resultset handler. The resultset must be in array form, i.e. an array of associative arrays
         * @author Art <a.molcanovas@gmail.com>
         */
        class Resultset extends ArrayObj {

            /**
             * Defines a modifier as "equals"
             * @var string
             */
            const MOD_EQUALS = '=';

            /**
             * Defines a modifier as "doesn't equal"
             * @var string
             */
            const MOD_NOT_EQUALS = '!=';

            /**
             * Defines a modifier as "greater than"
             * @var string
             */
            const MOD_GT = '>';

            /**
             * Defines a modifier as "greater than or equal to"
             * @var string
             */
            const MOD_GET = '>=';

            /**
             * Defines a modifier as "lower than"
             * @var string
             */
            const MOD_LT = '<';

            /**
             * Defines a modifier as "lower than or equal to"
             * @var string
             */
            const MOD_LET = '<=';

            /**
             * Initialises our resultset handler
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $resultset The resultset returned
             */
            function __construct(array $resultset) {
                parent::__construct($resultset);
            }

            /**
             * Get values matching the filter
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $spec  The filter associative array where the keys are column names and the values are
             *                     [modifier,value]. For example to get rows where the column "foo" is greater than 5
             *                     and the column "bar" equals "qux" pass the array ['foo' => ['>',5], 'bar' => ['=',
             *                     'qux']]
             * @param int   $limit Maximum number of rows to match
             *
             * @return array
             */
            function getWhere(array $spec, $limit = PHP_INT_MAX) {
                $count  = 0;
                $return = [];

                foreach ($this->data as $row) {
                    $passed = true;
                    foreach ($spec as $specKey => $specItem) {
                        if (!self::compare(get($row[$specKey]), get($specItem[0]), get($specItem[1]))) {
                            $passed = false;
                            break;
                        }
                    }

                    if ($passed) {
                        $return[] = $row;
                        if ((++$count) >= $limit) {
                            break;
                        }
                    }
                }

                return $return;
            }

            /**
             * Checks if a the row column matches the filter
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param mixed  $value    The value in the row
             * @param string $modifier The modifier ('=','>', '>=' etc)
             * @param mixed  $modValue What the modifier is checking against
             *
             * @return bool
             */
            protected static function compare($value, $modifier, $modValue) {
                switch ($modifier) {
                    case self::MOD_EQUALS:
                        return $value == $modValue;
                    case self::MOD_NOT_EQUALS:
                        return $value != $modValue;
                    case self::MOD_LT:
                        return $value < $modValue;
                    case self::MOD_LET:
                        return $value <= $modValue;
                    case self::MOD_GT:
                        return $value > $modValue;
                    case self::MOD_GET:
                        return $value >= $modValue;
                    default:
                        return false;
                }
            }

            /**
             * Only keeps vallues in the resultset that match the filter
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $spec  The filter associative array where the keys are column names and the values are
             *                     [modifier,value]. For example to get rows where the column "foo" is greater than 5
             *                     and the column "bar" equals "qux" pass the array ['foo' => ['>',5], 'bar' => ['=',
             *                     'qux']]
             * @param int   $limit Maximum number of rows to match
             *
             * @return Resultset
             */
            function keepWhere(array $spec, $limit = PHP_INT_MAX) {
                $this->data = $this->getWhere($spec, $limit);

                return $this;
            }

            /**
             * Deletes values from the resultset that match the filter
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $spec The filter associative array where the keys are column names and the values are
             *                    [modifier,value]. For example to get rows where the column "foo" is greater than 5
             *                    and the column "bar" equals "qux" pass the array ['foo' => ['>',5], 'bar' => ['=',
             *                    'qux']]
             *
             * @return Resultset
             */
            function deleteWhere(array $spec) {
                $get = $this->getWhere($spec);

                foreach ($this->data as $k => $row) {
                    $keep = true;

                    foreach ($get as $getRow) {
                        if ($row === $getRow) {
                            $keep = false;
                            break;
                        }
                    }

                    if (!$keep) {
                        unset($this->data[$k]);
                    }
                }

                return $this;
            }

            /**
             * A clone of getWhere() that returns references to the rows in the dataset instead of a new array
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $spec The filter associative array where the keys are column names and the values are
             *                    [modifier,value]. For example to get rows where the column "foo" is greater than 5
             *                    and the column "bar" equals "qux" pass the array ['foo' => ['>',5], 'bar' => ['=',
             *                    'qux']]
             *
             * @return array
             */
            protected function &getWhereReferential(array $spec) {
                $return = [];

                foreach ($this->data as &$row) {
                    $passed = true;
                    foreach ($spec as $specKey => $specItem) {
                        if (!self::compare(get($row[$specKey]), get($specItem[0]), get($specItem[1]))) {
                            $passed = false;
                            break;
                        }
                    }

                    if ($passed) {
                        $return[] = &$row;
                    }
                }

                return $return;
            }

            /**
             * Sets the value(s) on columns that match the filter
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $sets  Array of values to set where the keys are the column names and the values are the
             *                     values to set
             * @param array $where The filter associative array where the keys are column names and the values are
             *                     [modifier,value]. For example to get rows where the column "foo" is greater than 5
             *                     and the column "bar" equals "qux" pass the array ['foo' => ['>',5], 'bar' => ['=',
             *                     'qux']]
             * @param int   $limit Maximum number of rows to modify
             *
             * @return Resultset
             */
            function setValue(array $sets, array $where = null, $limit = PHP_INT_MAX) {
                if ($where) {
                    $get = &$this->getWhereReferential($where, $limit);
                } else {
                    $get = &$this->data;
                }

                foreach ($get as &$row) {
                    foreach ($sets as $k => $v) {
                        $row[$k] = $v;
                    }
                }

                return $this;
            }

            /**
             * Applies a callback to the rows matching the filter. This will modify the data in the object.
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param callable $callable The callback function. It should accept the first parameter which is the
             *                           array of associative arrays that matches the filter.
             * @param array    $where
             *
             * @return bool|mixed false if $callable isn't callable or whatever your callback returns.
             */
            function applyCallbackWhere($callable, $where = null) {
                if (!is_callable($callable)) {
                    phpWarning('The supplied callback isn\'t callable');

                    return false;
                } else {
                    if ($where) {
                        $get = &$this->getWhereReferential($where);
                    } else {
                        $get = &$this->data;
                    }

                    return call_user_func($callable, $get);
                }
            }

            /**
             * Appends values in the resultset
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $sets  Array of values to append where the keys are the column names and the values are
             *                     the strings to append
             * @param array $where The filter associative array where the keys are column names and the values are
             *                     [modifier,value]. For example to get rows where the column "foo" is greater than 5
             *                     and the column "bar" equals "qux" pass the array ['foo' => ['>',5], 'bar' => ['=',
             *                     'qux']]
             * @param int   $limit Maxumum number of affected rows
             *
             * @return Resultset
             */
            function appendValue(array $sets, array $where = null, $limit = PHP_INT_MAX) {
                if ($where) {
                    $get = &$this->getWhereReferential($where, $limit);
                } else {
                    $get = &$this->data;
                }

                foreach ($get as &$row) {
                    foreach ($sets as $k => $v) {
                        $row[$k] = $row[$k] . $v;
                    }
                }

                return $this;
            }

            /**
             * Prepends values in the resultset
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $sets  Array of values to prepend where the keys are the column names and the values are
             *                     the strings to prepend
             * @param array $where The filter associative array where the keys are column names and the values are
             *                     [modifier,value]. For example to get rows where the column "foo" is greater than 5
             *                     and the column "bar" equals "qux" pass the array ['foo' => ['>',5], 'bar' => ['=',
             *                     'qux']]
             * @param int   $limit Maximum number of affected rows
             *
             * @return Resultset
             */
            function prependValue(array $sets, array $where = null, $limit = PHP_INT_MAX) {
                if ($where) {
                    $get = &$this->getWhereReferential($where, $limit);
                } else {
                    $get = &$this->data;
                }

                foreach ($get as &$row) {
                    foreach ($sets as $k => $v) {
                        $row[$k] = $v . $row[$k];
                    }
                }

                return $this;
            }

            /**
             * Increments a numeric value(or values) in the resultset
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $sets  The increment specs, where the keys are the columns to modify and the values are
             *                     how much to increment by
             * @param array $where The filter associative array where the keys are column names and the values are
             *                     [modifier,value]. For example to get rows where the column "foo" is greater than 5
             *                     and the column "bar" equals "qux" pass the array ['foo' => ['>',5], 'bar' => ['=',
             *                     'qux']]
             * @param int   $limit Maxumum number of affected rows
             *
             * @return Resultset
             */
            function incrementValue(array $sets, array $where = null, $limit = PHP_INT_MAX) {
                if ($where) {
                    $get = &$this->getWhereReferential($where, $limit);
                } else {
                    $get = &$this->data;
                }

                foreach ($get as &$row) {
                    foreach ($sets as $k => $v) {
                        if (is_numeric($row[$k])) {
                            $row[$k] += $v;
                        }
                    }
                }

                return $this;
            }

            /**
             * Decrements a numeric value(or values) in the resultset
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $sets  The decrement specs where the keys are the columns to modify and the values are how
             *                     much to decrement by
             * @param array $where The filter associative array where the keys are column names and the values are
             *                     [modifier,value]. For example to get rows where the column "foo" is greater than 5
             *                     and the column "bar" equals "qux" pass the array ['foo' => ['>',5], 'bar' => ['=',
             *                     'qux']]
             * @param int   $limit Maximum number of affected rows
             *
             * @return Resultset
             */
            function decrementValue(array $sets, array $where = null, $limit = PHP_INT_MAX) {
                if ($where) {
                    $get = &$this->getWhereReferential($where, $limit);
                } else {
                    $get = &$this->data;
                }

                foreach ($get as &$row) {
                    foreach ($sets as $k => $v) {
                        if (is_numeric($row[$k])) {
                            $row[$k] -= $v;
                        }
                    }
                }

                return $this;
            }

            /**
             * Multiplies numeric values in the resultset
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $sets  The multiplication specs where the keys are columns to modify and the values are
             *                     how much to multiply by
             * @param array $where The filter associative array where the keys are column names and the values are
             *                     [modifier,value]. For example to get rows where the column "foo" is greater than 5
             *                     and the column "bar" equals "qux" pass the array ['foo' => ['>',5], 'bar' => ['=',
             *                     'qux']]
             * @param int   $limit Maximum number of affected rows
             *
             * @return Resultset
             */
            function multiplyValue(array $sets, array $where = null, $limit = PHP_INT_MAX) {
                if ($where) {
                    $get = &$this->getWhereReferential($where, $limit);
                } else {
                    $get = &$this->data;
                }

                foreach ($get as &$row) {
                    foreach ($sets as $k => $v) {
                        if (is_numeric($row[$k])) {
                            $row[$k] *= $v;
                        }
                    }
                }

                return $this;
            }

            /**
             * Divides numeric values in the resultset
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $sets  The division specs where the keys are columns to modify and the values are how much
             *                     divide by
             * @param array $where The filter associative array where the keys are column names and the values are
             *                     [modifier,value]. For example to get rows where the column "foo" is greater than 5
             *                     and the column "bar" equals "qux" pass the array ['foo' => ['>',5], 'bar' => ['=',
             *                     'qux']]
             * @param int   $limit Maximum number of affected rows
             *
             * @return Resultset
             */
            function divideValue(array $sets, array $where = null, $limit = PHP_INT_MAX) {
                if ($where) {
                    $get = &$this->getWhereReferential($where, $limit);
                } else {
                    $get = &$this->data;
                }

                foreach ($get as &$row) {
                    foreach ($sets as $k => $v) {
                        if (is_numeric($row[$k])) {
                            $row[$k] /= $v;
                        }
                    }
                }

                return $this;
            }

            /**
             * Transforms the dataset into an insert statement
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $tableName       Which what table to insert to
             * @param string $insertType      Insert type. Can be INSERT or REPLACE
             * @param bool   $withBoundParams If set to true (recommended) will produce an array
             *                                [sql_query_for_prepare_statement,array_of_bound_parameters]; if set to false will generate a simple
             *                                unescaped insert query which should only be used for internal testing
             *
             * @return array|bool|string false if the data array is empty or the insert type is invalid, the SQL
             * query string if $withBoundParams is false, [sql_query_for_prepare_statement,array_of_bound_parameters]
             * array if $withBoundParams is true
             */
            function toInsertStatement($tableName, $insertType = 'INSERT', $withBoundParams = true) {
                $insertType = strtoupper($insertType);
                if (!empty($this->data) && ($insertType == 'INSERT' || $insertType == 'REPLACE')) {
                    $sql = $insertType . ' INTO `' . $tableName . '`(`' . implode('`,`', array_keys($this->data[0])) .
                           '`) VALUES';
                    if ($withBoundParams) {
                        $params = [];

                        foreach ($this->data as $rowIdx => $row) {
                            $sql .= '(';
                            foreach ($row as $itemIdx => $item) {
                                $key = ':r' . $rowIdx . 'c' . $itemIdx;

                                $params[$key] = $item;
                                $sql .= $key . ',';
                            }
                            $sql = rtrim($sql, ',') . '),';
                        }

                        return [rtrim($sql, ','), $params];
                    } else {
                        foreach ($this->data as $row) {
                            $sql .= '(';
                            foreach ($row as $item) {
                                if (is_numeric($item)) {
                                    $sql .= $item . ',';
                                } elseif (!$item) {
                                    $sql .= 'NULL,';
                                } else {
                                    $sql .= '\'' . str_replace("'", "\\'", $item) . '\',';
                                }
                            }

                            $sql = rtrim($sql, ',') . '),';
                        }

                        return rtrim($sql, ',');
                    }
                } else {
                    return false;
                }
            }
        }
    }
