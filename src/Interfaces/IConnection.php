<?php

namespace crystlbrd\DatabaseHandler\Interfaces;

use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;

interface IConnection
{

    const JOIN_INNER = 1;
    const JOIN_LEFT = 2;
    const JOIN_RIGHT = 3;
    const JOIN_FULL = 4;
    const JOIN_CROSS = 5;

    /**
     * IConnection constructor.
     * @param string $host host
     * @param string $user user name
     * @param string $pass password
     * @param string $name database name
     * @param array $options additional options
     */
    public function __construct(string $host, string $user, string $pass, string $name, array $options = []);

    // Connection handling

    /**
     * Closes the connection
     * @return bool
     */
    public function closeConnection(): bool;

    /**
     * Deletes rows from table
     * @param string $table table name
     * @param array $conditions conditions
     * @return bool
     */
    public function delete(string $table, array $conditions): bool;

    /**
     * Returns the description for a table
     * @param string $table table name
     * @return array
     */
    public function describe(string $table): array;

    /**
     * Drops a database
     * @param string $database
     * @return bool true on success, false on error
     * @throws ConnectionException
     */
    public function dropDatabase(string $database): bool;

    /**
     * Drops a table
     * @param string $table
     * @return bool
     * @throws ConnectionException
     */
    public function dropTable(string $table): bool;

    /**
     * Inserts data into a table
     * @param string $table table name
     * @param array $data columns and data to insert
     * @return int inserted ID or 0 on error
     */
    public function insert(string $table, array $data): int;


    /**
     * Opens the connection
     * @return bool
     */
    public function openConnection(): bool;


    // Getter

    /**
     * Gets the saved database credentials
     * @param string|null $index
     * @return array|string
     */
    public function getCredentials(string $index = null);

    /**
     * Gets last database error
     * @return mixed
     */
    public function getLastError();

    /**
     * Gets last inserted ID
     * @return mixed
     */
    public function getLastInsertId();

    /**
     * Gets last executed SQL query
     * @return string
     */
    public function getLastQuery(): string;

    /**
     * Gets the options
     * @param string|null $index
     * @return array|string|null
     */
    public function getOptions(string $index = null);

    /**
     * Gets all executed SQL queries
     * @return array
     */
    public function getQueryHistory(): array;


    /**
     * Sends a SQL query to the database
     * @param string $sql SQL query
     * @return mixed received response
     */
    public function query(string $sql);

    /**
     * Selects rows from a table
     * @param string|array $tables one or more table names
     * @param array $columns columns to select
     * @param array $conditions conditions
     * @param array $options additional options
     * @return array
     */
    public function select($tables, array $columns, array $conditions = [], array $options = []): array;


    // Setters

    /**
     * Sets the host
     * @param string $host host
     * @return IConnection self for chainability
     */
    public function setHost(string $host): self;

    /**
     * Sets database name
     * @param string $name database name
     * @return IConnection self for chainability
     */
    public function setName(string $name): self;

    /**
     * Sets a option
     * @param string $option option index
     * @param mixed $value option value
     * @return IConnection self for chainability
     */
    public function setOption(string $option, $value): self;

    /**
     * Sets password
     * @param string $password password
     * @return IConnection self for chainability
     */
    public function setPassword(string $password): self;

    /**
     * Sets user name
     * @param string $user user name
     * @return IConnection self for chainability
     */
    public function setUser(string $user): self;


    /**
     * Updates rows in a table
     * @param string $table table name
     * @param array $columns columns and values to update
     * @param array $conditions conditions
     * @return bool
     */
    public function update(string $table, array $columns, array $conditions): bool;
}