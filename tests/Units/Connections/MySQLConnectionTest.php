<?php

namespace crystlbrd\Exceptionist\Tests\Units\Connections;

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

        $this->assertNotFalse($res);
        $this->assertSame('SELECT * FROM table1', $this->Connection->getLastQuery());
    }

    /**
     * @small
     */
    public function testSelectColumns()
    {
        // SELECT some columns
        $res = $this->Connection->select('table1', ['col1', 'col2' => 'alias']);

        $this->assertNotFalse($res);
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
                    'col1' => 'value1',
                    'col2' => 'value2',
                    'col3' => ['val1', 'val2']
                ]
            ]
        );

        $this->assertNotFalse($res);
        $this->assertSame('SELECT * FROM table1 WHERE col1 = "value1" AND col2 = "value2" AND col3 = "val1" AND col3 = "val2"', $this->Connection->getLastQuery());
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
                    'a' => 'b',
                    'c' => 'd',
                    ['e' => 'f', 'g' => 'h'],
                    'i' => ['k', 'l']
                ]
            ]
        );

        $this->assertNotFalse($res);
        $this->assertSame('SELECT * FROM table1 WHERE a = "b" OR c = "d" OR e = "f" AND g = "h" OR i = "k" OR i = "l"', $this->Connection->getLastQuery());
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

        $this->assertNotFalse($res);

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

        self::assertNotFalse($res);

        $expect = 'SELECT * FROM table1 WHERE ';

        $expect .= 'col1 = "val1"';
        $expect .= ' AND col2 > "val2.1" AND col2 > "val2.2"';
        $expect .= ' AND col3 >= "val3.1" AND col3 >= "val3.2"';
        $expect .= ' AND col4 < "val4.1" AND col4 < "val4.2"';
        $expect .= ' AND col5 <= "val5.1" AND col5 <= "val5.2"';
        $expect .= ' AND col6 LIKE "val6.1" AND col6 LIKE "val6.2"';

        self::assertSame($expect, $this->Connection->getLastQuery());
    }
}