<?php

namespace crystlbrd\DatabaseHandler\Tests\Mocks;

use crystlbrd\DatabaseHandler\Connections\MySQLConnection;
use PDO;
use PDOStatement;
use Prophecy\Argument;
use Prophecy\Prophet;

class TestingMySQLConnection extends MySQLConnection
{
    private $Prophet;

    public function __construct(string $host, string $user, string $pass, string $name, array $options = [])
    {
        parent::__construct($host, $user, $pass, $name, $options);
        $this->Prophet = new Prophet();
    }

    public function openConnection(): bool
    {
        $pdo = $this->Prophet->prophesize(PDO::class);
        $stm = $this->Prophet->prophesize(PDOStatement::class);

        $stm
            ->execute()
            ->willReturn(true);

        $pdo
            ->prepare(Argument::type('string'))
            ->willReturn($stm->reveal());

        $this->PDO = $pdo->reveal();

        return true;
    }

    public function getLastError(): array
    {
        return [];
    }

    public function getLastInsertId()
    {
        return 0;
    }
}