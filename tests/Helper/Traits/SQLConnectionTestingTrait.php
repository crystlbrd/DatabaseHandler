<?php


namespace crystlbrd\DatabaseHandler\Tests\Helper\Traits;

use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
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

    /**
     * Defines the expected parameters to query translations for the select method
     * @return SQLIterator
     */
    abstract function expectedSelectSQLTranslations(): SQLIterator;

    /**
     * Defines the expected parameters to query translations for the insert method
     * @return SQLIterator
     */
    abstract function expectedInsertSQLTranslations(): SQLIterator;

    /**
     * Defines the expected parameters to query translations for the update method
     * @return SQLIterator
     */
    abstract function expectedUpdateSQLTranslations(): SQLIterator;

    /**
     * Defines the ConnectionClass
     */
    abstract function config(): void;

    /**
     * Tests IConnection::select()
     * @param array $tables
     * @param array $columns
     * @param array $conditions
     * @param array $options
     * @param string $expectedString
     * @author crystlbrd
     * @dataProvider expectedSelectSQLTranslations
     */
    public function testSelectSQLParsing(array $tables, array $columns, array $conditions, array $options, string $expectedString): void
    {
        // open the connection
        $this->initConnection();

        // send the select request
        $this->Connection->select($tables, $columns, $conditions, $options);

        // check the generated query
        self::assertSame($expectedString, $this->Connection->getLastQuery());
    }

    /**
     * Generates a random hex value
     * @return string
     */
    private function getRandomHexValue(): string
    {
        return dechex(rand(1000000000000000, 9999999999999999));
    }

    /**
     * Tests the getters and setters
     * @author crystlbrd
     */
    public function testGetterAndSetter(): void
    {
        // open the connection
        $this->initConnection();

        // define some random values
        $host = $this->getRandomHexValue();
        $user = $this->getRandomHexValue();
        $pass = $this->getRandomHexValue();
        $name = $this->getRandomHexValue();
        $order = ['col' => $this->getRandomHexValue()];
        $group = $this->getRandomHexValue();

        // set data
        $this->Connection->setHost($host);
        $this->Connection->setUser($user);
        $this->Connection->setPassword($pass);
        $this->Connection->setName($name);

        $this->Connection->setOption('order', $order);
        $this->Connection->setOption('group', $group);

        // check API
        self::assertIsArray($this->Connection->getCredentials());
        self::assertIsArray($this->Connection->getOptions());

        // get data
        self::assertSame($host, $this->Connection->getCredentials('host'));
        self::assertSame($user, $this->Connection->getCredentials('user'));
        self::assertSame($name, $this->Connection->getCredentials('name'));
        self::assertSame($pass, $this->Connection->getCredentials('pass'));

        self::assertSame($order, $this->Connection->getOptions('order'));
        self::assertSame($group, $this->Connection->getOptions('group'));
        self::assertSame(null, $this->Connection->getOptions('something_completely_random'));
    }

    /**
     * Tests the update method
     * @param string $table
     * @param array $data
     * @param string $expectedString
     * @author cryslbrd
     * @dataProvider expectedInsertSQLTranslations
     */
    public function testInsertSQLParsing(string $table, array $data, string $expectedString): void
    {
        // open the connection
        $this->initConnection();

        // send the select request
        $this->Connection->insert($table, $data);

        // check the generated query
        self::assertSame($expectedString, $this->Connection->getLastQuery());
    }

    /**
     * Tests the update method
     * @param string $table
     * @param array $data
     * @param array $conditions
     * @param string $expectedString
     * @author crysltbrd
     * @dataProvider expectedUpdateSQLTranslations
     */
    public function testUpdateSQLParsing(string $table, array $data, array $conditions, string $expectedString): void
    {
        // open the connection
        $this->initConnection();

        // send the select request
        $this->Connection->update($table, $data, $conditions);

        // check the generated query
        self::assertSame($expectedString, $this->Connection->getLastQuery());
    }

    /**
     * Test the DROP methods
     */
    public function testDropSQLParsing()
    {
        // open the connection
        $this->initConnection();

        // drop database
        $this->Connection->dropDatabase('dropdatabase123');

        // check SQL
        self::assertSame('DROP DATABASE dropdatabase123;', $this->Connection->getLastQuery());

        // drop table
        $this->Connection->dropTable('droptable123');

        // check SQL
        self::assertSame('DROP TABLE droptable123;', $this->Connection->getLastQuery());
    }

    /**
     * Tests the delete method
     */
    public function testDelete()
    {
    }
}