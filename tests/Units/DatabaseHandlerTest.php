<?php


namespace crystlbrd\DatabaseHandler\Tests\Units;


use crystlbrd\DatabaseHandler\DatabaseHandler;
use crystlbrd\DatabaseHandler\Exceptions\DatabaseHandlerException;
use crystlbrd\DatabaseHandler\Interfaces\IConnection;
use crystlbrd\DatabaseHandler\Table;
use crystlbrd\DatabaseHandler\Tests\Helper\TestCases\DatabaseTestCase;

class DatabaseHandlerTest extends DatabaseTestCase
{
    public function testAddConnection(): DatabaseHandler
    {
        // Init DatabaseHandler
        $dbh = new DatabaseHandler();

        // add new connection
        self::assertTrue($dbh->addConnection('default', $this->DefaultConnection));

        // test if connection exists
        self::assertTrue($dbh->connectionExists('default'));

        // try to add the connection again
        self::assertFalse($dbh->addConnection('default', $this->DefaultConnection));

        // get connection
        $activeConn = $dbh->getActiveConnection();
        $conn = $dbh->getConnection('default');

        self::assertSame($activeConn, $conn);

        self::assertNotFalse($conn);
        self::assertInstanceOf(IConnection::class, $conn);
        self::assertSame($this->DefaultConnection, $conn);

        return $dbh;
    }

    /**
     * @author crystlbrd
     * @depends testAddConnection
     * @param DatabaseHandler $dbh
     */
    public function testRemoveConnection(DatabaseHandler $dbh): void
    {
        // check, if the connection exists
        self::assertTrue($dbh->connectionExists('default'));

        // remove connection
        self::assertTrue($dbh->removeConnection('default'));

        // check for connection
        self::assertFalse($dbh->connectionExists('default'));

        // try to remove it again
        self::assertFalse($dbh->removeConnection('default'));
    }

    /**
     * @author crystlbrd
     * @throws DatabaseHandlerException
     */
    public function testLoad()
    {
        // init
        $dbh = new DatabaseHandler();

        // add connection
        $dbh->addConnection('test', $this->DefaultConnection);

        // try to load a table
        $table1 = $dbh->load('table1');
        self::assertInstanceOf(Table::class, $table1);

        // try to load another table
        $table2 = $dbh->load('table2');
        self::assertInstanceOf(Table::class, $table2);
    }
}