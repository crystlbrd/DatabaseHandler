<?php

namespace crystlbrd\Exceptionist\Tests\Units\Connections;

use crystlbrd\DatabaseHandler\DatabaseHandler;
use crystlbrd\DatabaseHandler\Result;
use crystlbrd\DatabaseHandler\Tests\DatabaseTestTrait;
use PHPUnit\Framework\TestCase;

class MySQLConnectionTest extends TestCase
{
    use DatabaseTestTrait;

    protected $Connection;
    protected $DatabaseHandler;

    protected function setUp(): void
    {
        // set up the database
        $this->setUpDatabase();

        // get the connection reverence
        $this->Connection = $this->getMySQLConnection();

        // init the DatabaseHandler
        $this->DatabaseHandler = new DatabaseHandler();

        // add the connection
        $this->DatabaseHandler->addConnection('mysql', $this->Connection);

        // open the connection
        $this->Connection->openConnection();
    }

    /**
     * @small
     */
    public function testGetterAndSetter()
    {
        // Get
        $this->assertIsArray($this->Connection->getCredentials());

        $this->assertSame($this->Connection->getCredentials('host'), $_ENV['db_host']);
        $this->assertSame($this->Connection->getCredentials('user'), $_ENV['db_user']);
        $this->assertSame($this->Connection->getCredentials('pass'), $_ENV['db_pass']);
        $this->assertSame($this->Connection->getCredentials('name'), $_ENV['db_name']);
    }

    # TODO rewrite tests
}