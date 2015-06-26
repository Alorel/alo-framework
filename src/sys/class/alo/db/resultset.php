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

            const MOD_EQUALS     = '=';
            const MOD_NOT_EQUALS = '!=';
            const MOD_GT         = '>';
            const MOD_GET        = '>=';
            const MOD_LT         = '<';
            const MOD_LET        = '<=';

            /**
             * Initialises our resultset handler
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param array $resultset The resultset returned
             */
            function __construct(array $resultset) {
                parent::__construct($resultset);
            }

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

            function keepWhere(array $spec, $limit = PHP_INT_MAX) {
                $this->data = $this->getWhere($spec, $limit);
            }

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
            }

            protected function &getWhereReferential(array $spec, $limit) {
                $count  = 0;
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
                        if ((++$count) >= $limit) {
                            break;
                        }
                    }
                }

                return $return;
            }

            function setValue(array $sets, array  $where = null, $limit = PHP_INT_MAX) {
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
            }

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
            }

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
            }

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
            }

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
            }

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
            }

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
            }
        }
    }
