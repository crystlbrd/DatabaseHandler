<?php


namespace crystlbrd\DatabaseHandler\Tests\Units;

use crystlbrd\DatabaseHandler\Table;
use PHPUnit\Framework\TestCase;
use crystlbrd\DatabaseHandler\DatabaseHandler;
use crystlbrd\DatabaseHandler\Connections\MySQLConnection;

class DatabaseHandlerTest extends TestCase
{
    protected function getMysqlConnectionStub()
    {
        $conn = $this->createStub(MySQLConnection::class);
        $conn->method('openConnection')->willReturn(true);

        return $conn;
    }

    /**
     * Tests, if it's possible to add and access a MySQLConnection to the DatabaseHandler
     * @throws \crystlbrd\DatabaseHandler\Exceptions\DatabaseHandlerException
     */
    public function testAddMysqlConnection()
    {
        // define connection
        $MySQLConnection = $this->getMysqlConnectionStub();

        // init DBH
        $dbh = new DatabaseHandler();

        // add connection
        $dbh->addConnection('mysql', $MySQLConnection);

        // test, if it's available
        self::assertTrue($dbh->connectionExists('mysql'));
        self::assertSame($MySQLConnection, $dbh->getConnection('mysql'));
    }

    /**
     * Test, if the DatabaseHandler behaves correctly, if you try to access a connection, that isn't defined
     */
    public function testAccessingNotExistingConnection()
    {
        $dbh = new DatabaseHandler();

        self::assertFalse($dbh->connectionExists('connection'));
        self::assertNull($dbh->getConnection('connection'));
    }

    public function testLoadingATable()
    {
        // init dbh
        $dbh = new DatabaseHandler();

        // add connection
        $dbh->addConnection('mysql', $this->getMysqlConnectionStub());

        // load table
        $table = $dbh->load('table');

        // test interface
        self::assertInstanceOf(Table::class, $table);
    }
}