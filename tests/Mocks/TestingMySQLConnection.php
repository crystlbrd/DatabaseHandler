<?php

namespace crystlbrd\DatabaseHandler\Tests\Mocks;

use crystlbrd\DatabaseHandler\Connections\MySQLConnection;
use crystlbrd\DatabaseHandler\Connections\PDOConnection;
use crystlbrd\DatabaseHandler\Drivers\MySQLPDODriver;
use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
use crystlbrd\DatabaseHandler\IConnection;
use crystlbrd\Exceptionist\Environment;
use Prophecy\Argument;
use Prophecy\Prophet;

class TestingMySQLConnection extends MySQLConnection
{
    use PDOConnection;
    use MySQLPDODriver;

    protected $Prophet;

    public function __construct(string $host, string $user, string $pass, string $name, array $options = [])
    {
        parent::__construct($host, $user, $pass, $name, $options);

        $this->Prophet = new Prophet();
    }

    public function openConnection(): bool
    {
        if ($this->PDO === null) {
            // Mocking PDO
            $prophecy_pdo = $this->Prophet->prophesize();
            $prophecy_pdo->willExtend('\PDO');

            $prophecy_stm = $this->Prophet->prophesize();
            $prophecy_stm->willExtend('\PDOStatement');

            // Setting up stubs
            $prophecy_pdo->prepare(Argument::type('string'))->willReturn($prophecy_stm->reveal());

            $this->PDO = $prophecy_pdo->reveal();
            return true;
        } else {
            return true;
        }
    }
}