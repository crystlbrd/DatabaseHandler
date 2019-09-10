<?php

namespace crystlbrd\Exceptionist\Tests\Units\Connections;

use crystlbrd\DatabaseHandler\Connections\MySQLConnection;
use crystlbrd\DatabaseHandler\Connections\PDOConnection;
use crystlbrd\DatabaseHandler\Tests\Helper\Iterator\InsertSQLIterator;
use crystlbrd\DatabaseHandler\Tests\Helper\Iterator\SelectSQLIterator;
use crystlbrd\DatabaseHandler\Tests\Helper\Iterator\SQLIterator;
use crystlbrd\DatabaseHandler\Tests\Helper\TestCases\DatabaseTestCase;
use crystlbrd\DatabaseHandler\Tests\Helper\Traits\SQLConnectionTestingTrait;

class MySQLConnectionTest extends DatabaseTestCase
{
    use SQLConnectionTestingTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config();
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
        return new SelectSQLIterator(PDOConnection::COLUMN_SEPERATOR, PDOConnection::ALIAS_SEPERATOR);
    }

    /**
     * Defines the expected parameters to query translations for the insert method
     * @return SQLIterator
     */
    function expectedInsertSQLTranslations(): SQLIterator
    {
        return new InsertSQLIterator();
    }
}