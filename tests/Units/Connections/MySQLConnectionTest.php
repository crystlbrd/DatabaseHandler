<?php

namespace crystlbrd\Exceptionist\Tests\Units\Connections;

use crystlbrd\DatabaseHandler\DatabaseHandler;
use crystlbrd\DatabaseHandler\IConnection;
use crystlbrd\DatabaseHandler\RowList;
use crystlbrd\DatabaseHandler\Tests\Mocks\TestingMySQLConnection;
use PHPUnit\Framework\TestCase;

class MySQLConnectionTest extends TestCase
{
    protected $Connection;

    protected function setUp(): void
    {
        // the credentials aren't actually relevant
        $this->Connection = new TestingMySQLConnection('', '', '', '');

        // "open" the connection
        $this->Connection->openConnection();
    }

    /**
     * @small
     */
    public function testGetterAndSetter()
    {
        // Set
        $this->Connection
            ->setHost('localhost')
            ->setUser('root')
            ->setPassword('toor')
            ->setName('database');

        // Get
        $this->assertIsArray($this->Connection->getCredentials());

        $this->assertSame($this->Connection->getCredentials('host'), 'localhost');
        $this->assertSame($this->Connection->getCredentials('user'), 'root');
        $this->assertSame($this->Connection->getCredentials('pass'), 'toor');
        $this->assertSame($this->Connection->getCredentials('name'), 'database');
    }

    /**
     * @small
     */
    public function testSelectAll()
    {
        // SELECT *
        $res = $this->Connection->select('table1');

        self::assertInstanceOf(RowList::class, $res);
        $this->assertSame('SELECT * FROM table1', $this->Connection->getLastQuery());
    }

    /**
     * @small
     */
    public function testSelectColumns()
    {
        // SELECT some columns
        $res = $this->Connection->select('table1', ['col1', 'col2' => 'alias']);

        self::assertInstanceOf(RowList::class, $res);
        $this->assertSame('SELECT col1, col2 AS alias FROM table1', $this->Connection->getLastQuery());
    }

    /**
     * @small
     */
    public function testSelectWithAndConditions()
    {
        // SELECT with only AND conditions
        $res = $this->Connection->select(
            'table1', [],
            [
                'and' => [
                    'col1' => 'val1',
                    'col2' => 'val2',
                    'col3' => ['val3.1', 'val3.2']
                ]
            ]
        );

        self::assertInstanceOf(RowList::class, $res);
        $this->assertSame('SELECT * FROM table1 WHERE col1 = "val1" AND col2 = "val2" AND col3 = "val3.1" AND col3 = "val3.2"', $this->Connection->getLastQuery());
    }

    /**
     * @small
     */
    public function testSelectWithOrConditions()
    {
        // SELECT with only OR conditions
        $res = $this->Connection->select(
            'table1', [],
            [
                'or' => [
                    'col1' => 'val1',
                    'col2' => 'val2',
                    ['col3' => 'val3', 'col4' => 'val4'],
                    'col5' => ['val5.1', 'val5.2']
                ]
            ]
        );

        self::assertInstanceOf(RowList::class, $res);
        $this->assertSame('SELECT * FROM table1 WHERE col1 = "val1" OR col2 = "val2" OR col3 = "val3" AND col4 = "val4" OR col5 = "val5.1" OR col5 = "val5.2"', $this->Connection->getLastQuery());
    }

    /**
     * @small
     */
    public function testSelectionWithMixedConditions()
    {
        // SELECT with AND and OR conditions
        $res = $this->Connection->select(
            'table1', [],
            [
                'and' => [
                    'col1' => 'val1',
                    'col2' => 'val2',
                    'col3' => ['val3.1', 'val3.2']
                ],
                'or' => [
                    'col4' => 'val4',
                    'col5' => 'val5',
                    ['col6' => 'val6', 'col7' => 'val7'],
                    'col8' => ['val8.1', 'val8.2']
                ]
            ]
        );

        self::assertInstanceOf(RowList::class, $res);

        // Expected SQL
        $sql = 'SELECT * FROM table1 WHERE ';
        $and = ' AND col1 = "val1" AND col2 = "val2" AND col3 = "val3.1" AND col3 = "val3.2"';

        $sql .= 'col4 = "val4"' . $and;
        $sql .= ' OR col5 = "val5"' . $and;
        $sql .= ' OR col6 = "val6" AND col7 = "val7"' . $and;
        $sql .= ' OR col8 = "val8.1"' . $and;
        $sql .= ' OR col8 = "val8.2"' . $and;

        $this->assertSame($sql, $this->Connection->getLastQuery());
    }

    public function testOperatorParsing()
    {
        // Select with different operators
        $res = $this->Connection->select(
            'table1', [],
            [
                'and' => [
                    'col1' => 'val1',
                    'col2' => ['> val2.1', '>{{val2.2}}'],
                    'col3' => ['>= val3.1', '>={{val3.2}}'],
                    'col4' => ['< val4.1', '<{{val4.2}}'],
                    'col5' => ['<= val5.1', '<={{val5.2}}'],
                    'col6' => ['~ val6.1', '~ {{val6.2}}'],
                ]
            ]
        );

        self::assertInstanceOf(RowList::class, $res);

        $expect = 'SELECT * FROM table1 WHERE ';

        $expect .= 'col1 = "val1"';
        $expect .= ' AND col2 > "val2.1" AND col2 > "val2.2"';
        $expect .= ' AND col3 >= "val3.1" AND col3 >= "val3.2"';
        $expect .= ' AND col4 < "val4.1" AND col4 < "val4.2"';
        $expect .= ' AND col5 <= "val5.1" AND col5 <= "val5.2"';
        $expect .= ' AND col6 LIKE "val6.1" AND col6 LIKE "val6.2"';

        self::assertSame($expect, $this->Connection->getLastQuery());
    }

    public function testOptionParsing()
    {
        $res = $this->Connection->select('table1', [], [], [
            'order' => ['col1' => 'asc', 'col2' => 'desc'],
            'group' => 'col3'
        ]);

        self::assertNotFalse($res);
        self::assertSame('SELECT * FROM table1 GROUP BY col3 ORDER BY col1, col2 DESC', $this->Connection->getLastQuery());
    }

    public function testJoinedSelect()
    {
        $res = $this->Connection->select([
            'table1',
            'table2' => [
                'join' => IConnection::JOIN_INNER,
                'on' => 'col2 = table1.col1'
            ],
            'table3' => [
                'join' => IConnection::JOIN_LEFT,
                'on' => 'col3 = table1.col1'
            ],
            'table4' => [
                'join' => IConnection::JOIN_RIGHT,
                'on' => 'col4 = table1.col1'
            ],
            'table5' => [
                'join' => IConnection::JOIN_FULL,
                'on' => 'col5 = table1.col1'
            ],
            'table6' => [
                'join' => IConnection::JOIN_CROSS,
                'on' => 'col6 = table1.col1'
            ]
        ]);

        self::assertInstanceOf(RowList::class, $res);

        $expect = 'SELECT * FROM table1';

        $expect .= ' INNER JOIN table2 ON table2.col2 = table1.col1';
        $expect .= ' LEFT JOIN table3 ON table3.col3 = table1.col1';
        $expect .= ' RIGHT JOIN table4 ON table4.col4 = table1.col1';
        $expect .= ' FULL OUTER JOIN table5 ON table5.col5 = table1.col1';
        $expect .= ' CROSS JOIN table6 ON table6.col6 = table1.col1';

        self::assertSame($expect, $this->Connection->getLastQuery());
    }
}