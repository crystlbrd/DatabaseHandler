<?php


namespace crystlbrd\DatabaseHandler\Tests\Units\Connections;


use crystlbrd\DatabaseHandler\Connections\MySQLConnection;
use crystlbrd\DatabaseHandler\Connections\PDOConnection;
use crystlbrd\DatabaseHandler\Tests\Helper\Iterator\InsertSQLIterator;
use crystlbrd\DatabaseHandler\Tests\Helper\Iterator\SelectSQLIterator;
use crystlbrd\DatabaseHandler\Tests\Helper\Iterator\SQLIterator;
use crystlbrd\DatabaseHandler\Tests\Helper\Iterator\UpdateSQLIterator;
use crystlbrd\DatabaseHandler\Tests\Helper\Traits\SQLConnectionTestingTrait;
use crystlbrd\DatabaseHandler\Tests\Mocks\TestingMySQLConnection;
use PHPUnit\Framework\TestCase;

class MysqlConnectionTest extends TestCase
{
    use SQLConnectionTestingTrait;

    protected function setUp(): void
    {
        $this->config();
    }


    function expectedSelectSQLTranslations(): SQLIterator
    {
        return new SelectSQLIterator(PDOConnection::COLUMN_SEPERATOR, PDOConnection::ALIAS_SEPERATOR);
    }

    function expectedInsertSQLTranslations(): SQLIterator
    {
        return new InsertSQLIterator();
    }

    function expectedUpdateSQLTranslations(): SQLIterator
    {
        return new UpdateSQLIterator();
    }

    function config(): void
    {
        $this->setConnectionClass(TestingMySQLConnection::class);
    }
}