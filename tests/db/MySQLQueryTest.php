<?php

    use Alo\Db\Query\MySQLQuery;

    class MySQLQueryTest extends \PHPUnit_Framework_TestCase {

        function testQuery() {
            $sql = new MySQLQuery(PhuGlobal::$mysql);
            $sql->select(['foo', 'bar', '`qux`'])
                ->from('footable')
                ->innerJoin('bartable', '`bartable`.`id`=`footable`.`id`')
                ->andWhere('foo', '>', 5)
                ->andWhere('`foo`', '<=', 10, false)
                ->whereBracketOpen()
                ->andWhere('bar', '!=', 4)
                ->orWhere('qux', '=', 'val')
                ->whereBracketClose()
                ->orWhere('lastCol', '>=', 111)
                ->limit(5, 4);

        }
    }
