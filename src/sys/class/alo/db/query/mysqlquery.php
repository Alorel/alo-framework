<?php

    namespace Alo\Db\Query;

    use PDO;
    use Alo\Db\MySQL;
    use Alo\Exception\LibraryException as Libex;
    use Alo\Exception\ORMException as ORMEx;
    use Alo;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } else {

        /**
         * MySQL ORM
         * @author Art <a.molcanovas@gmail.com>
         */
        class MySQLQuery extends AbstractQuery {

            /**
             * Reference to database connection
             * @var MySQL|PDO
             */
            protected $db;

            function __construct(&$db = null) {
                if ($db && ($db instanceof MySQL || $db instanceof PDO)) {
                    $this->db = &$db;
                } elseif (Alo::$db && (Alo::$db instanceof MySQL || Alo::$db instanceof PDO)) {
                    $this->db = &Alo::$db;
                } else {
                    throw new Libex('Database instance not found', Libex::E_REQUIRED_LIB_NOT_FOUND);
                }
            }

            /**
             * Returns a string representation of the built query
             * @author Art <a.molcanovas@gmail.com>
             * @return string
             * @throws ORMEx When there are no columns to select or there is no source table
             */
            function getSQL() {
                $sql = '';
                if (empty($this->select)) {
                    throw new ORMEx('No columns to select', ORMEx::E_INVALID_QUERY);
                } elseif (!$this->from) {
                    throw new ORMEx('No source table', ORMEx::E_INVALID_QUERY);
                } else {
                    $sql = 'SELECT ' . implode(',', $this->select) . ' FROM ' . $this->from;

                    foreach ($this->joins as $j) {
                        $sql .= ' ' . $j['type'] . ' JOIN ' . $j['table'];
                        if ($j['on']) {
                            $sql .= ' ON ' . $j['on'];
                        }
                    }

                    if (!empty($this->where)) {
                        $sql .= ' WHERE';

                        $insertBracket = null;
                        $count         = 0;
                        $max           = count($this->where) - 1;

                        foreach ($this->where as $w) {
                            if (is_string($w)) {
                                if ($w == ')' && $count == $max) {
                                    $sql .= ')';
                                } else {
                                    $insertBracket = $w;
                                }
                            } else {
                                if ($insertBracket == ')') {
                                    $sql .= ')';
                                }

                                if ($count != 0) {
                                    $sql .= ' ' . $w['kind'];
                                }

                                if ($insertBracket == '(') {
                                    $sql .= '(';
                                }

                                $sql .= ' ' . $w['col'] . $w['mod'] . $w['val'];

                                $insertBracket = null;
                            }

                            $count++;
                        }
                    }
                }

                return $sql;
            }
        }
    }
