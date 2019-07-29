<?php

namespace crystlbrd\DatabaseHandler\Connections;

use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
use crystlbrd\DatabaseHandler\IConnection;
use crystlbrd\Exceptionist\Environment;
use crystlbrd\Exceptionist\ExceptionistTrait;
use PDO;
use PDOException;

abstract class PDOConnection implements IConnection
{
    use ExceptionistTrait;

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
            'reporting' => Environment::E_LEVEL_ERROR
        ], $options);
    }

    /**
     * Opens the connection
     * @return bool
     * @throws ConnectionException
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

    public function getCredentials($index = null)
    {
        $credentials = [
            'host' => $this->Host,
            'user' => $this->User,
            'pass' => $this->Pass,
            'name' => $this->Name
        ];

        if ($index != null && isset($credentials[$index])) {
            return $credentials[$index];
        } else {
            return $credentials;
        }
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
}