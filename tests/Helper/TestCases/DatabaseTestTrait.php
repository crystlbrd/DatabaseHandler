<?php

namespace crystlbrd\DatabaseHandler\Tests\Helper\TestCases;

use PDO;
use PHPUnit\Framework\TestCase;

class DatabaseTestCase extends TestCase
{
    public function setUpDatabase(): void
    {
        // connect to database
        $pdo = new PDO('mysql:host=' . $_ENV['db_host'] . ';', $_ENV['db_user'], $_ENV['db_pass']);

        // delete database
        self::assertNotFalse($pdo->query('DROP DATABASE IF EXISTS ' . $_ENV['db_name']));

        // create database
        self::assertNotFalse($pdo->query('CREATE DATABASE ' . $_ENV['db_name']));

        // use it
        self::assertNotFalse($pdo->query('USE ' . $_ENV['db_name']));

        // create tables
        self::assertNotFalse($pdo->query('CREATE TABLE IF NOT EXISTS table1 (col1 INT UNSIGNED PRIMARY KEY, col2 VARCHAR(256), col3 FLOAT)'));
        self::assertNotFalse($pdo->query('CREATE TABLE IF NOT EXISTS table2 (col1 INT UNSIGNED PRIMARY KEY, col2 TEXT, ref_table1 INT UNSIGNED)'));

        // set constraint
        self::assertNotFalse($pdo->query('ALTER TABLE table2 ADD CONSTRAINT FOREIGN KEY (ref_table1) REFERENCES table1 (col1) ON UPDATE no action ON DELETE no action'), json_encode($pdo->errorInfo()));
    }
}