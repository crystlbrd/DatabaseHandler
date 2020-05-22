<?php

namespace crystlbrd\DatabaseHandler\Interfaces;

interface ISQLParser
{
    /**
     * Returns all bound values
     * @return array Bound values (placeholder => value)
     */
    public function getBoundValues(): array;

    /**
     * Gets the value of a specific placeholder
     * @param string $placeholder Placeholder
     * @return null|string Value
     */
    public function getValueOf(string $placeholder): ?string;

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
     * @param bool $usePlaceholders Bind the values behind a placeholder (useful for binding values)
     * @param string $placeholderTemplate Placeholder template
     * @return string SELECT query
     */
    public function select(array $tables, array $columns = [], array $where = [], array $options = [], bool $usePlaceholders = true, string $placeholderTemplate = ':param'): string;

    /**
     * Generates a UPDATE query
     * @param string $table Table to update
     * @param array $data Data (column => value)
     * @param array $where WHERE conditions
     * @return string UPDATE query
     */
    public function update(string $table, array $data, array $where = []): string;
}