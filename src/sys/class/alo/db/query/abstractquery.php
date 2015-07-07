<?php

    namespace Alo\Db\Query;

    use Alo\Exception\ORMException as Ormex;
    use Alo\Exception\ORMException;
    use PDO;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * Abstract ORM
         * @author Art <a.molcanovas@gmail.com>
         */
        abstract class AbstractQuery {

            /**
             * Bound parameters
             * @var array
             */
            protected $binds;

            /**
             * The LIMIT clause
             * @var string
             */
            protected $limit;

            /**
             * JOINs to perform
             * @var array
             */
            protected $joins;

            /**
             * Columns to select
             * @var array
             */
            protected $select;

            /**
             * Where clauses
             * @var array
             */
            protected $where;

            /**
             * Table to select from
             * @var string
             */
            protected $from;

            /**
             * Resets the query settings
             * @return AbstractQuery
             */
            function reset() {
                $this->binds = $this->joins = $this->select = $this->where = [];
                $this->limit = null;

                return $this;
            }

            /**
             * Sets the table to select from
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $table Table name
             *
             * @return AbstractQuery
             * @throws ORMEx When $table isn't scalar
             */
            function from($table) {
                if (is_string($table)) {
                    $this->from = $table;
                } else {
                    throw new Ormex('$table must be scalar', ORMex::E_INVALID_DATATYPE);
                }

                return $this;
            }

            /**
             * Returns a string representation of the built query
             * @author Art <a.molcanovas@gmail.com>
             * @return string
             */
            abstract function getSQL();

            /**
             * Sets an INNER JOIN
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $table Table to join
             * @param string $on    ON condition
             *
             * @return AbstractQuery
             * @throws ORMException When $table or $on isn't a string
             */
            function innerJoin($table, $on) {
                return $this->abstractJoin($table, $on, 'INNER');
            }

            /**
             * The abstract joining method
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $table Table to join
             * @param string $on    ON condition
             * @param string $type  JOIN type
             *
             * @return AbstractQuery
             * @throws ORMException When $table or $on isn't a string
             */
            protected function abstractJoin($table, $on, $type) {
                if (!is_string($table)) {
                    throw new ORMEx('$table must be a string', ORMEx::E_INVALID_DATATYPE);
                } elseif (!is_string($on) && $on !== null) {
                    throw new ORMException('$on must be a string', ORMEx::E_INVALID_DATATYPE);
                } else {
                    $this->joins[] = ['type'  => $type,
                                      'table' => $table,
                                      'on'    => $on];
                }

                return $this;
            }

            /**
             * Performs a CROSS JOIN
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $table Table to join
             *
             * @return AbstractQuery
             * @throws ORMException When $table isn't a string
             */
            function crossJoin($table) {
                return $this->abstractJoin($table, null, 'CROSS');
            }

            /**
             * performs a LEFT JOIN
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $table Table to join
             * @param string $on    ON condition
             *
             * @return AbstractQuery
             * @throws ORMException When $table or $on isn't a string
             */
            function leftJoin($table, $on) {
                return $this->abstractJoin($table, $on, 'LEFT');
            }

            /**
             * performs a RIGHT JOIN
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $table Table to join
             * @param string $on    ON condition
             *
             * @return AbstractQuery
             * @throws ORMException When $table or $on isn't a string
             */
            function rightJoin($table, $on) {
                return $this->abstractJoin($table, $on, 'RIGHT');
            }

            /**
             * Adds a WHERE clause. If it's not the first WHERE clause, they will be linked by AND
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $column   WHERE $column
             * @param string $modifier WHERE $column $modifier
             * @param string $value    WHERE $column $modifier $value
             * @param bool   $bind     Whether to use PDO parameter binding. It is HIGHLY discouraged to set this to false.
             *
             * @return AbstractQuery
             */
            function andWhere($column, $modifier, $value, $bind = true) {
                return $this->abstractWhere($column, $modifier, $value, $bind, 'AND');
            }

            /**
             * The abstract WHERE builder
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $column   WHERE $column
             * @param string $modifier WHERE $column $modifier
             * @param string $value    WHERE $column $modifier $value
             * @param bool   $bind     Whether to use PDO parameter binding. It is HIGHLY discouraged to set this to false.
             * @param string $kind     OR/AND (how to link multiple WHEREs)
             *
             * @return AbstractQuery
             */
            protected function abstractWhere($column, $modifier, $value, $bind, $kind) {
                $add = ['col'  => $column,
                        'mod'  => $modifier,
                        'kind' => $kind];

                if ($bind) {
                    $bind               = ':w' . md5($column . $modifier . $value);
                    $this->binds[$bind] = ['val'  => $value,
                                           'type' => is_numeric($value) && substr($value, 0, 1) != '0' ?
                                               PDO::PARAM_INT : $value === null ? PDO::PARAM_NULL : PDO::PARAM_STR];
                    $add['val']         = $bind;
                } else {
                    $add['val'] = '\'' . str_replace('\'', "\\'", $value) . '\'';
                }

                $this->where[] = $add;

                return $this;
            }

            /**
             * Adds a WHERE clause. If it's not the first WHERE clause, they will be linked by OR
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string $column   WHERE $column
             * @param string $modifier WHERE $column $modifier
             * @param string $value    WHERE $column $modifier $value
             * @param bool   $bind     Whether to use PDO parameter binding. It is HIGHLY discouraged to set this to false.
             *
             * @return AbstractQuery
             */
            function orWhere($column, $modifier, $value, $bind = true) {
                return $this->abstractWhere($column, $modifier, $value, $bind, 'OR');
            }

            /**
             * Opens a bracket in the WHERE clause
             * @author Art <a.molcanovas@gmail.com>
             * @return AbstractQuery
             */
            function whereBracketOpen() {
                $this->where[] = '(';

                return $this;
            }

            /**
             * Closes the bracket in the WHERE clause
             * @author Art <a.molcanovas@gmail.com>
             * @return AbstractQuery
             */
            function whereBracketClose() {
                $this->where[] = ')';

                return $this;
            }

            /**
             * Adds a column or array of columns to the SELECT clause
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param string|array $col The column or array of columns
             *
             * @return AbstractQuery
             * @throws ORMException When $col isn't a string or array of strings
             */
            function select($col) {
                if (is_array($col)) {
                    foreach ($col as $c) {
                        $this->select($c);
                    }
                } elseif (is_string($col)) {
                    $this->select[] = $col;
                } else {
                    throw new ORMEx('$col must be a string or array of strings', Ormex::E_INVALID_DATATYPE);
                }

                return $this;
            }

            /**
             * Sets the LIMIT clause
             * @author Art <a.molcanovas@gmail.com>
             *
             * @param int      $limit1 If $limit2 is not passed, the maximum amount of rows to return, otherwise the
             *                         return start index desired
             * @param int|null $limit2 The max amount of rows to return
             *
             * @return AbstractQuery
             * @throws ORMException When $limit1 or $limit2 are not integers
             */
            function limit($limit1, $limit2 = null) {
                if (!is_numeric($limit1)) {
                    throw new ORMEx('$limit1 must be numeric', ORMEx::E_INVALID_DATATYPE);
                } else {
                    if ($limit2 === null) {
                        $this->limit = $limit1;
                    } elseif (!is_numeric($limit2)) {
                        throw new ORMEx('$limit2 must be numeric', ORMEx::E_INVALID_DATATYPE);
                    } else {
                        $this->limit = $limit1 . ',' . $limit2;
                    }
                }

                return $this;
            }

            /**
             * Returns the list of bound parameters
             * @author Art <a.molcanovas@gmail.com>
             * @return array
             */
            function getBinds() {
                $r = [];

                if ($this->binds) {
                    foreach ($this->binds as $k => $v) {
                        $r[$k] = $v['val'];
                    }
                }

                return $r;
            }

            /**
             * Returns a string representation of the built query
             * @author Art <a.molcanovas@gmail.com>
             * @return string
             */
            function __toString() {
                return $this->getSQL();
            }
        }
    }
