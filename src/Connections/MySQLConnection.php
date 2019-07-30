<?php

namespace crystlbrd\DatabaseHandler\Connections;

use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
use crystlbrd\DatabaseHandler\RowList;
use crystlbrd\Exceptionist\ExceptionistTrait;

class MySQLConnection extends PDOConnection
{
    use ExceptionistTrait;

    /**
     * Selects rows from a table
     * @param string|array $tables one or more table names
     * @param array $columns columns to select
     * @param array $conditions conditions
     * @param array $options additional options
     * @return RowList
     * @throws ConnectionException
     */
    public function select($tables, array $columns = [], array $conditions = [], array $options = []): RowList
    {
        // SELECT
        $sql = 'SELECT';

        // COLUMNS
        $sql .= ' ' . $this->parseColumns($columns);

        // FROM
        $sql .= ' FROM ' . $this->parseTables($tables);

        // WHERE
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . $this->parseConditions($conditions) . ' ';
        }

        // ADDITIONAL OPTIONS
        if (!empty($options)) {
            $sql .= $this->parseOptions($options);
        }

        return new RowList($this, $this->execute($sql));
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
        // TODO: [v1] Implement update() method.
    }

    /**
     * Inserts data into a table
     * @param string $table table name
     * @param array $data columns and data to insert
     * @return int inserted ID or 0 on error
     */
    public function insert(string $table, array $data): int
    {
        // TODO: [v1] Implement insert() method.
    }

    /**
     * Deletes rows from table
     * @param string $table table name
     * @param array $conditions conditions
     * @return bool
     */
    public function delete(string $table, array $conditions): bool
    {
        // TODO: [v1] Implement delete() method.
    }
}