<?php

    use Alo\Db\Query\MySQLQuery;

    class MySQLQueryTest extends \PHPUnit_Framework_TestCase {

        function testQuery() {
            $sql = new MySQLQuery(PhuGlobal::$mysql);
            $sql->select(['foo', 'bar', '`qux`'])
                ->from('footable')
                ->innerJoin('bartable', '`bartable`.`id`=`footable`.`id`')
                ->andWhere('foo', '>', 5, false)
                ->andWhere('`foo`', '<=', 10, false)
                ->whereBracketOpen()
                ->andWhere('bar', '!=', 4, false)
                ->orWhere('qux', '=', 'val', false)
                ->whereBracketClose()
                ->orWhere('lastCol', '>=', 111, false)
                ->limit(5, 4);

            $xpect =
                'SELECT foo,bar,`qux` ' . 'FROM footable ' . 'INNER JOIN bartable ON `bartable`.`id`=`footable`.`id` ' .
                'WHERE foo>\'5\' ' . 'AND `foo`<=\'10\' ' . 'AND( bar!=\'4\' OR qux=\'val\') ' . 'OR lastCol>=\'111\'';

            $this->assertEquals($xpect, $sql->getSQL());

        }
    }
