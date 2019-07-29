<?php

namespace crystlbrd\DatabaseHandler;

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
     * Opens the connection
     * @return bool
     */
    public function openConnection(): bool;

    /**
     * Closes the connection
     * @return bool
     */
    public function closeConnection(): bool;

    // Setters

    /**
     * Sets the host
     * @param string $host host
     * @return IConnection self for chainability
     */
    public function setHost(string $host): self;

    /**
     * Sets user name
     * @param string $user user name
     * @return IConnection self for chainability
     */
    public function setUser(string $user): self;

    /**
     * Sets password
     * @param string $password password
     * @return IConnection self for chainability
     */
    public function setPassword(string $password): self;

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

    // Getter

    /**
     * Gets all executed SQL queries
     * @return array
     */
    public function getQueryHistory(): array;

    /**
     * Gets last executed SQL query
     * @return string
     */
    public function getLastQuery(): string;

    /**
     * Gets last database error
     * @return mixed
     */
    public function getLastError();

    /**
     * Gets last inserted ID
     * @return int
     */
    public function getLastInsertId(): int;

    // Database manipulation

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
     * @return RowList
     */
    public function select($tables, array $columns = [], array $conditions = [], array $options = []): RowList;

    /**
     * Updates rows in a table
     * @param string $table table name
     * @param array $columns columns and values to update
     * @param array $conditions conditions
     * @return bool
     */
    public function update(string $table, array $columns, array $conditions): bool;

    /**
     * Inserts data into a table
     * @param string $table table name
     * @param array $data columns and data to insert
     * @return int inserted ID or 0 on error
     */
    public function insert(string $table, array $data): int;

    /**
     * Deletes rows from table
     * @param string $table table name
     * @param array $conditions conditions
     * @return bool
     */
    public function delete(string $table, array $conditions): bool;
}