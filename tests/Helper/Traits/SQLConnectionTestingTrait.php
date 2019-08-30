<?php


namespace crystlbrd\DatabaseHandler\Tests\Helper\Traits;

use crystlbrd\DatabaseHandler\Interfaces\IConnection;
use crystlbrd\DatabaseHandler\Tests\Helper\Iterator\SQLIterator;

trait SQLConnectionTestingTrait
{
    protected $ConnectionClass;

    /**
     * @var IConnection
     */
    protected $Connection;

    /**
     * Defines the Connection class to be used for the tests
     * @param string $class
     */
    protected final function setConnectionClass(string $class): void
    {
        $this->ConnectionClass = $class;
    }

    /**
     * Opens the database connection
     */
    public function initConnection(): void
    {
        // check if connection already initialised
        if (!$this->Connection) {
            // create new instance
            $this->Connection = new $this->ConnectionClass($_ENV['db_host'], $_ENV['db_user'], $_ENV['db_pass'], $_ENV['db_name']);

            // open connection
            $this->Connection->openConnection();
        }
    }

    abstract function expectedSelectSQLTranslations(): SQLIterator;

    /**
     * Tests IConnection::select()
     * @dataProvider expectedSelectSQLTranslations
     * @param array $tables
     * @param array $columns
     * @param array $conditions
     * @param array $options
     * @param string $expectedString
     */
    public function testSelectSQLParsing(array $tables, array $columns, array $conditions, array $options, string $expectedString)
    {
        // open the connection
        $this->initConnection();

        // test the set
        $this->Connection->select($tables, $columns, $conditions, $options);
        self::assertSame($expectedString, $this->Connection->getLastQuery());
    }

    /**
     * Defines Separators and ConnectionClass
     */
    abstract function config(): void;
}