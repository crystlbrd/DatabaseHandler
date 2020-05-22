<?php

namespace crystlbrd\DatabaseHandler\Interfaces;

interface ISQLParser
{
    /**
     * Returns all values replaced with an placeholder
     * @return array Bound values (placeholder => value)
     */
    public function getValues(): array;

    /**
     * Gets the next available placeholder
     * @return string
     */
    public function getPlaceholder(): string;

    /**
     * Gets the current template for naming placeholders
     * @return string
     */
    public function getPlaceholderTemplate(): string;

    /**
     * Gets the value of a specific placeholder
     * @param string $placeholder Placeholder
     * @return null|mixed Value
     */
    public function getValueOf(string $placeholder);

    /**
     * Generates an INSERT query
     * @param string $table Table where to insert to
     * @param array $data Data (column => value)
     * @param bool $usePlaceholders Replace the value with a placeholder (useful for binding values)
     * @return string INSERT query
     */
    public function insert(string $table, array $data, bool $usePlaceholders = false): string;

    /**
     * Deletes replaced values and resets placeholder generation
     */
    public function resetPlaceholders(): void;

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
     * @param bool $usePlaceholders Replace the value with a placeholder (useful for binding values)
     * @return string SELECT query
     */
    public function select(array $tables, array $columns = [], array $where = [], array $options = [], bool $usePlaceholders = false): string;

    /**
     * Defines the template to use for naming placeholders
     * @param string $template
     */
    public function setPlaceholderTemplate(string $template): void;

    /**
     * Generates a UPDATE query
     * @param string $table Table to update
     * @param array $data Data (column => value)
     * @param array $where WHERE conditions
     * @param bool $usePlaceholders Replace the value with a placeholder (useful for binding values)
     * @return string UPDATE query
     */
    public function update(string $table, array $data, array $where = [], bool $usePlaceholders = false): string;
}