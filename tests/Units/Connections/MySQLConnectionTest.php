<?php

namespace crystlbrd\Exceptionist\Tests\Units\Connections;

use crystlbrd\DatabaseHandler\Connections\MySQLConnection;
use crystlbrd\DatabaseHandler\Connections\PDOConnection;
use crystlbrd\DatabaseHandler\Tests\Helper\Iterator\SQLIterator;
use crystlbrd\DatabaseHandler\Tests\Helper\TestCases\DatabaseTestCase;
use crystlbrd\DatabaseHandler\Tests\Helper\Traits\SQLConnectionTestingTrait;
use PHPUnit\Framework\TestCase;

class MySQLConnectionTest extends DatabaseTestCase
{
    use SQLConnectionTestingTrait;

    protected function setUp(): void
    {
        $this->config();
        $this->setUpDatabase();
    }

    /**
     * Defines Separators and ConnectionClass (required by SQLConnectionTestingTrait)
     */
    private function config(): void
    {
        $this->setConnectionClass(MySQLConnection::class);
    }

    public function expectedSelectSQLTranslations()
    {
        return new SQLIterator(PDOConnection::COLUMN_SEPERATOR, PDOConnection::ALIAS_SEPERATOR);
    }
}