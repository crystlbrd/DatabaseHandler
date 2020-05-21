<?php

namespace crystlbrd\DatabaseHandler\Interfaces;

interface ISQLParser
{
    /**
     * Generates an INSERT query
     * @param string $table Table where to insert to
     * @param array $data Data (column => value)
     * @return string INSERT query
     */
    public function insert(string $table, array $data): string;

    /**
     * Generates a SELECT query
     * @param array $tables Tables to select from
     * @param array $columns Columns to select
     * @param array $where WHERE conditions
     * @param array $options Additional options
     * Possible Options:
     * - order (array): ORDER BY
     * - group (array): GROUP BY
     * - limit (int|string): LIMIT
     * @return string SELECT query
     */
    public function select(array $tables, array $columns = [], array $where = [], array $options = []): string;

    /**
     * Generates a UPDATE query
     * @param string $table Table to update
     * @param array $data Data (column => value)
     * @param array $where WHERE conditions
     * @return string UPDATE query
     */
    public function update(string $table, array $data, array $where = []): string;
}