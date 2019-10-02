<?php

namespace crystlbrd\DatabaseHandler;

use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
use crystlbrd\DatabaseHandler\Exceptions\DatabaseHandlerException;
use crystlbrd\DatabaseHandler\Exceptions\TableException;
use crystlbrd\DatabaseHandler\Interfaces\IConnection;
use crystlbrd\Exceptionist\Environment;
use crystlbrd\Exceptionist\ExceptionistTrait;

class DatabaseHandler
{
    use ExceptionistTrait;

    /**
     * @var array options
     */
    protected $Options;

    /**
     * @var array connection interfaces
     */
    private $Connections = [];

    /**
     * @var string pointing on the current used connection
     */
    private $ConnectionPointer;

    /**
     * DatabaseHandler constructor.
     * @param array $options additional options
     */
    public function __construct(array $options = [])
    {
        // load options
        $this->Options = array_merge($options, [
            'reporting' => Environment::E_LEVEL_ERROR
        ]);
    }

    /**
     * Checks if there is a connection with a given name
     * @param string $name the connection name
     * @return bool
     */
    public function connectionExists(string $name): bool
    {
        return isset($this->Connections[$name]);
    }

    /**
     * Adds a connection to the handler
     * @param string $name the connection name
     * @param IConnection $connection
     * @return bool
     * @throws DatabaseHandlerException
     */
    public function addConnection(string $name, IConnection $connection): bool
    {
        // connection already defined?
        if (!$this->connectionExists($name)) {
            // save connection interface
            $this->Connections[$name] = $connection;

            // set the pointer
            return $this->use($name);
        } else {
            // log if not
            $this->log(new DatabaseHandlerException('Connection with name ' . $name . ' already defined!'), Environment::E_LEVEL_WARNING);
            return false;
        }
    }

    /**
     * Removes a connection by its name
     * @param string $name the connection name
     * @return bool
     * @throws DatabaseHandlerException
     */
    public function removeConnection(string $name): bool
    {
        // connection defined?
        if ($this->connectionExists($name)) {
            // close connection
            $this->Connections[$name]->closeConnection();

            // unset interface
            unset($this->Connections[$name]);

            // return a happy true
            return true;
        } else {
            // just log it this time
            $this->log(new DatabaseHandlerException('No connection with name ' . $name . ' found!'), Environment::E_LEVEL_WARNING);
            return false;
        }
    }

    /**
     * Returns a connection by name
     * @param string $name the internally defined name
     * @return IConnection|null
     */
    public function getConnection(string $name): ?IConnection
    {
        if (isset($this->Connections[$name])) {
            return $this->Connections[$name];
        } else {
            return null;
        }
    }

    /**
     * Returns the currently selected connections
     * @return IConnection
     */
    public function getActiveConnection(): IConnection
    {
        return $this->Connections[$this->ConnectionPointer];
    }

    /**
     * Selects a connection
     * @param string $name the connection name
     * @return bool
     * @throws \Exception
     */
    public function use(string $name): bool
    {
        // Connection defined?
        if ($this->connectionExists($name)) {
            // set pointer
            $this->ConnectionPointer = $name;

            // open connection if needed
            try {
                return $this->Connections[$this->ConnectionPointer]->openConnection();
            } catch (ConnectionException $e) {
                // chain exceptions
                $this->log(new DatabaseHandlerException('Failed to set pointer on ' . $name . '!', $e), Environment::E_LEVEL_ERROR);
            }
        } else {
            // throw exception if not
            $this->log(new DatabaseHandlerException('No connection with name ' . $name . ' found!'), Environment::E_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * Loads a table form the database
     * @param string $table
     * @return Table
     */
    public function load(string $table): Table
    {
        // init the table
        return new Table(
            $this->getActiveConnection(),
            $table
        );
    }

    /**
     * Deltes a  database from the currently selected connection
     * @param string $database
     * @return bool true on success, false on failure
     * @throws DatabaseHandlerException
     */
    public function deleteDatabase(string $database): bool
    {
        try {
            return $this->getActiveConnection()->dropDatabase($database);
        } catch (ConnectionException $e) {
            $this->log(new DatabaseHandlerException('Something went terribly wrong while deleting the database "' . $database . '"!', $e), Environment::E_LEVEL_ERROR);
        }
    }

    /**
     * Deletes a table from the currently selected connection
     * @param string $table
     * @return bool true on success, false on failure
     * @throws DatabaseHandlerException
     */
    public function deleteTable(string $table): bool
    {
        try {
            return $this->getActiveConnection()->dropTable($table);
        } catch (ConnectionException $e) {
            $this->log(new DatabaseHandlerException('Something went terribly wrong while deleting the table "' . $table . '"!', $e), Environment::E_LEVEL_ERROR);
        }
    }

    /**
     * Returns the last error of the currently active connection
     * @return mixed
     */
    public function getLastError()
    {
        return $this->getActiveConnection()->getLastError();
    }
}