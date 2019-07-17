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
}