<?php

namespace crystlbrd\DatabaseHandler;

interface IRowList
{
    /**
     * IRowList constructor.
     * @param IConnection $connection current connection
     * @param mixed $data received results
     */
    public function __construct(IConnection $connection, Table $table, $data);

    /**
     * Fetches a row from the results
     * @return bool|Row
     */
    public function fetch();

    /**
     * Returns all rows from the result as a numeric array
     * @return array
     */
    public function fetchAll(): array;

    /**
     * Returns the amount of rows in the result
     * @return int
     */
    public function countRows(): int;

    /**
     * Sets the pointer again to the beginning
     */
    public function reset(): void;

    /**
     * Updates all rows
     * @param array $data array(column => new value)
     * @return bool
     */
    public function update(array $data): bool;

    /**
     * Deletes all rows from the data source
     * @return bool
     */
    public function delete(): bool;
}