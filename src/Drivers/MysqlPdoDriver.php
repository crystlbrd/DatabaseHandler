<?php

namespace crystlbrd\DatabaseHandler\Drivers;

use crystlbrd\DatabaseHandler\RowList;

trait MysqlPdoDriver
{

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