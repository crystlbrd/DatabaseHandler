<?php

namespace crystlbrd\DatabaseHandler\Connections;

use crystlbrd\DatabaseHandler\DatabaseHandler;
use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
use crystlbrd\DatabaseHandler\IConnection;
use crystlbrd\DatabaseHandler\Logger;
use crystlbrd\DatabaseHandler\RowList;
use PDO;
use PDOException;

class MySQLConnection implements IConnection
{
    use Logger;

    /**
     * @var string host
     */
    protected $Host;

    /**
     * @var string user name
     */
    protected $User;

    /**
     * @var string password
     */
    protected $Pass;

    /**
     * @var string database name
     */
    protected $Name;

    /**
     * @var array options
     */
    protected $Options = [];

    /**
     * @var PDO connection
     */
    protected $PDO;

    /**
     * @var array SQL query history
     */
    protected $History = [];

    /**
     * IConnection constructor.
     * @param string $host host
     * @param string $user user name
     * @param string $pass password
     * @param string $name database name
     * @param array $options additional options
     */
    public function __construct(string $host, string $user, string $pass, string $name, array $options = [])
    {
        // save parameters
        $this->Host = $host;
        $this->User = $user;
        $this->Pass = $pass;
        $this->Name = $name;

        // save options and defaults
        $this->Options = array_merge([
            'encoding' => 'utf8mb4',
            'port' => 3306,
            'reporting' => DatabaseHandler::E_REPORTING_ERRORS
        ], $options);
    }

    /**
     * Opens the connection
     * @return bool
     */
    public function openConnection(): bool
    {
        // Connection already opened?
        if ($this->PDO === null) {
            try {
                /// build dst

                // host
                $dst = 'mysql:host=' . $this->Host . ';';

                // db name
                $dst .= 'dbname=' . $this->Name . ';';

                // encoding
                $dst .= 'charset=' . $this->Options['encoding'];

                // port
                $dst .= 'port=' . $this->Options['port'];

                // open connection
                $this->PDO = new PDO($dst, $this->User, $this->Pass);

                // we are happy and returning true
                return true;
            } catch (PDOException $e) {
                $this->log(new ConnectionException('Failed to connect to database!', $e), 'error');
                return false;
            }
        } else {
            // just log the info
            $this->log(new ConnectionException('Connection already opened.'), 'debug');

            // but it's not an error, so return true
            return true;
        }
    }

    /**
     * Closes the connection
     * @return bool
     */
    public function closeConnection(): bool
    {
        // Just delete the PDO object
        $this->PDO = null;

        // and return a happy true
        return true;
    }

    /**
     * Sets the host name
     * @param string $host host name
     * @return IConnection self for chainability
     */
    public function setHost(string $host): IConnection
    {
        $this->Host = $host;
        return $this;
    }

    /**
     * Sets user name
     * @param string $user user name
     * @return IConnection self for chainability
     */
    public function setUser(string $user): IConnection
    {
        $this->User = $user;
        return $this;
    }

    /**
     * Sets password
     * @param string $password password
     * @return IConnection self for chainability
     */
    public function setPassword(string $password): IConnection
    {
        $this->Pass = $password;
        return $this;
    }

    /**
     * Sets database name
     * @param string $name database name
     * @return IConnection self for chainability
     */
    public function setName(string $name): IConnection
    {
        $this->Name = $name;
        return $this;
    }

    /**
     * Sets a option
     * @param string $option option index
     * @param mixed $value option value
     * @return IConnection self for chainability
     */
    public function setOption(string $option, $value): IConnection
    {
        $this->Options[$option] = $value;
        return $this;
    }

    /**
     * Gets all executed SQL queries
     * @return array
     */
    public function getQueryHistory(): array
    {
        return $this->History;
    }

    /**
     * Gets last executed SQL query
     * @return string
     */
    public function getLastQuery(): string
    {
        return array_values(array_slice($this->History, -1))[0];
    }

    /**
     * Gets last database error
     * @return array
     */
    public function getLastError(): array
    {
        return $this->PDO->errorInfo();
    }

    /**
     * Gets last inserted ID
     * @return int
     */
    public function getLastInsertId(): int
    {
        $id = $this->PDO->lastInsertId();
        return intval($id);
    }

    /**
     * Selects rows from a table
     * @param string $table table name
     * @param array $columns columns to select
     * @param array $conditions conditions
     * @param array $options additional options
     * @return RowList
     */
    public function select(string $table, array $columns, array $conditions, array $options = []): RowList
    {
        // TODO: Implement select() method.
    }

    /**
     * Updates rows in a table
     * @param string $table table name
     * @param array $columns columns and values to update
     * @param array $conditions conditions
     * @return bool
     */
    public function update(string $table, array $columns, array $conditions): bool
    {
        // TODO: Implement update() method.
    }

    /**
     * Inserts data into a table
     * @param string $table table name
     * @param array $data columns and data to insert
     * @return int inserted ID or 0 on error
     */
    public function insert(string $table, array $data): int
    {
        // TODO: Implement insert() method.
    }

    /**
     * Deletes rows from table
     * @param string $table table name
     * @param array $conditions conditions
     * @return bool
     */
    public function delete(string $table, array $conditions): bool
    {
        // TODO: Implement delete() method.
    }
}