<?php

namespace crystlbrd\DatabaseHandler\Connections;

use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
use crystlbrd\DatabaseHandler\Exceptions\ParserException;
use crystlbrd\DatabaseHandler\Parser\PDOParser;
use crystlbrd\Exceptionist\Environment;
use crystlbrd\Exceptionist\ExceptionistTrait;
use PDO;

class MySQLConnection extends PDOConnection
{
    use ExceptionistTrait;

    /**
     * Selects rows from a table
     * @param string|array $tables one or more table names
     * @param array $columns columns to select
     * @param array $conditions conditions
     * @param array $options additional options
     * @return array
     * @throws ConnectionException
     * @throws ParserException
     */
    public function select($tables, array $columns, array $conditions = [], array $options = []): array
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

        // execute SQL
        $result = $this->execute($sql);

        if ($result !== false) {
            // Parse result
            return PDOParser::parse($result);
        } else {
            // Throw an exception if
            $this->log(new ConnectionException('Failed to select data from ' . json_encode($tables) . '!'), Environment::E_LEVEL_ERROR);

            // return an empty array if exceptions are disabled
            return [];
        }
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

    /**
     * Returns the description for a table
     * @param string $table table name
     * @return array
     * @throws ConnectionException
     */
    public function describe(string $table): array
    {
        // build SQL
        $sql = 'DESCRIBE ' . $table;

        // execute
        $stm = $this->execute($sql);

        // parse
        $result = [];
        while ($r = $stm->fetch(PDO::FETCH_ASSOC)) {
            $result[$r['Field']] = [
                'type' => $r['Type'],
                'null' => $r['Null'],
                'key' => $r['Key'],
                'Default' => $r['Default'],
                'Extra' => $r['Extra']
            ];
        }

        // return
        return $result;
    }
}