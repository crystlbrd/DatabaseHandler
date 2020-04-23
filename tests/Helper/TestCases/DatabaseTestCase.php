<?php

namespace crystlbrd\DatabaseHandler\Tests\Helper\TestCases;

use crystlbrd\DatabaseHandler\Connections\MySQLConnection;
use PDO;
use PHPUnit\Framework\TestCase;

class DatabaseTestCase extends TestCase
{
    protected $DefaultConnection;

    protected function setUp(): void
    {
        ### RESET DATABASE

        // connect to database
        $pdo = new PDO('mysql:host=' . $_ENV['db_host'] . ';', $_ENV['db_user'], $_ENV['db_pass']);

        // prepare database
        $this->assertNotFalse($pdo->query(file_get_contents(dirname(__FILE__) . '/../../TestDB/testdb.sql')));

        ### SET DEFAULTS

        $this->DefaultConnection = new MySQLConnection($_ENV['db_host'], $_ENV['db_user'], $_ENV['db_pass'], $_ENV['db_name']);
    }
}