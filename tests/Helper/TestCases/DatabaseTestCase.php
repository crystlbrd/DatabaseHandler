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

        // delete database
        self::assertNotFalse($pdo->query('DROP DATABASE IF EXISTS ' . $_ENV['db_name']));

        // create database
        self::assertNotFalse($pdo->query('CREATE DATABASE ' . $_ENV['db_name']));

        // use it
        self::assertNotFalse($pdo->query('USE ' . $_ENV['db_name']));

        // create tables
        self::assertNotFalse($pdo->query('CREATE TABLE IF NOT EXISTS table1 (col1 INT UNSIGNED PRIMARY KEY AUTO_INCREMENT, col2 VARCHAR(256), col3 FLOAT)'));
        self::assertNotFalse($pdo->query('CREATE TABLE IF NOT EXISTS table2 (col1 INT UNSIGNED PRIMARY KEY AUTO_INCREMENT, col2 TEXT, ref_table1 INT UNSIGNED)'));

        // set constraint
        self::assertNotFalse($pdo->query('ALTER TABLE table2 ADD CONSTRAINT FOREIGN KEY (ref_table1) REFERENCES table1 (col1) ON UPDATE no action ON DELETE no action'), json_encode($pdo->errorInfo()));

        // add data
        self::assertNotFalse($pdo->query('INSERT INTO table1 (col2, col3) VALUES ("val1.1", 0.1)'));
        self::assertNotFalse($pdo->query('INSERT INTO table1 (col2, col3) VALUES ("val1.2", 0.2)'));
        self::assertNotFalse($pdo->query('INSERT INTO table1 (col2, col3) VALUES ("val1.3", 0.3)'));
        self::assertNotFalse($pdo->query('INSERT INTO table1 (col2, col3) VALUES ("val1.4", 0.4)'));
        self::assertNotFalse($pdo->query('INSERT INTO table1 (col2, col3) VALUES ("val1.5", 0.5)'));

        self::assertNotFalse($pdo->query('INSERT INTO table2 (col2, ref_table1) VALUES ("val2.1", 1)'));
        self::assertNotFalse($pdo->query('INSERT INTO table2 (col2, ref_table1) VALUES ("val2.2", 1)'));
        self::assertNotFalse($pdo->query('INSERT INTO table2 (col2, ref_table1) VALUES ("val2.3", 2)'));
        self::assertNotFalse($pdo->query('INSERT INTO table2 (col2, ref_table1) VALUES ("val2.4", 3)'));
        self::assertNotFalse($pdo->query('INSERT INTO table2 (col2, ref_table1) VALUES ("val2.5", 5)'));

        ### SET DEFAULTS

        $this->DefaultConnection = new MySQLConnection($_ENV['db_host'], $_ENV['db_user'], $_ENV['db_pass'], $_ENV['db_name']);
    }
}