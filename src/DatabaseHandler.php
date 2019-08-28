<?php

namespace crystlbrd\DatabaseHandler;

use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
use crystlbrd\DatabaseHandler\Exceptions\DatabaseHandlerException;
use crystlbrd\DatabaseHandler\Exceptions\TableException;
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

    public function __construct(array $options = [])
    {
        // load options
        $this->Options = array_merge($options, [
            'reporting' => Environment::E_LEVEL_ERROR
        ]);
    }

    public function connectionExists(string $name): bool
    {
        return isset($this->Connections[$name]);
    }

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

    public function getActiveConnection()
    {
        return $this->Connections[$this->ConnectionPointer];
    }

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

    public function load(string $table): Table
    {
        try {
            // init the table
            return new Table($this->getActiveConnection(),
                $table);
        } catch (TableException $e) {
            $this->log(new DatabaseHandlerException('Failed to load table ' . $table . '!', $e), Environment::E_LEVEL_ERROR);
        }
    }
}