<?php

namespace crystlbrd\DatabaseHandler\Connections;

use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
use crystlbrd\DatabaseHandler\Exceptions\ParserException;
use crystlbrd\DatabaseHandler\Parser\PDOParser;
use Exception;
use PDO;

class MySQLConnection extends PDOConnection
{
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

        // adding end ;
        $sql .= ';';

        // execute SQL
        $result = $this->execute($sql);

        if ($result !== false) {
            // Parse result
            return PDOParser::parse($result);
        } else {
            // Throw an exception on error
            throw new ConnectionException('Failed to select data from ' . json_encode($tables) . '!',
                    ConnectionException::EXCP_CODE_QUERY_EXECUTION_FAILED,
                    // log the PDO error as an own exception
                    new ConnectionException(
                        json_encode(
                            array_merge(
                                ['query' => $this->getLastQuery()],
                                $this->getLastError()
                            )
                        ),
                        ConnectionException::EXCP_CODE_PDO_ERROR
                    )
                );
        }
    }

    /**
     * Updates rows in a table
     * @param string $table table name
     * @param array $columns columns and values to update
     * @param array $conditions conditions
     * @return bool
     * @throws ConnectionException
     */
    public function update(string $table, array $columns, array $conditions = []): bool
    {
        // UPDATE
        $sql = 'UPDATE ' . $table . ' SET ';

        // COLUMNS
        $i = 0;
        foreach ($columns as $column => $value) {
            $sql .= ($i != 0 ? ' , ' : '') . $column . $this->parseValue($value);
            $i++;
        }

        // WHERE
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . $this->parseConditions($conditions);
        }

        // END
        $sql .= ' ;';

        // EXECUTE
        return !!($this->execute($sql));
    }

    /**
     * Inserts data into a table
     * @param string $table table name
     * @param array $data columns and data to insert
     * @return int inserted ID or 0 on error
     * @throws ConnectionException
     */
    public function insert(string $table, array $data): int
    {
        // INSERT
        $sql = 'INSERT INTO ' . $table . ' (';

        // COLUMNS
        $i = 0;
        foreach ($data as $column => $value) {
            $sql .= ($i != 0 ? ', ' : '') . $column;
            $i++;
        }

        // VALUES
        $sql .= ') VALUES ( ';

        $i = 0;
        foreach ($data as $column => $value) {
            $sql .= ($i != 0 ? ' , ' : '') . $this->bindParam($value);
            $i++;
        }

        // END
        $sql .= ' );';

        // execute
        $result = $this->execute($sql);

        // return the last inserted ID if successful
        if ($result) {
            return $this->getLastInsertId();
        } else {
            // or return 0 (false) on failure
            return 0;
        }
    }

    /**
     * Deletes rows from table
     * @param string $table table name
     * @param array $conditions conditions
     * @return bool
     * @throws ConnectionException
     */
    public function delete(string $table, array $conditions = []): bool
    {
        // DELETE FROM
        $sql = 'DELETE FROM ' . $table;

        // WHERE
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . $this->parseConditions($conditions);
        }

        // END
        $sql .= ';';

        // execute
        return !!($this->execute($sql));
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
        if ($stm) {
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
        } else {
            throw new ConnectionException(
                'Failed to load description for "' . $table . '"!',
                new Exception(json_encode($this->getLastError()))
            );
        }
    }

    /**
     * Drops a database
     * @param string $database
     * @return bool true on success, false on error
     * @throws ConnectionException
     */
    public function dropDatabase(string $database): bool
    {
        // DROP DATABASE
        $sql = 'DROP DATABASE ' . $database . ';';

        return !!($this->execute($sql));
    }

    /**
     * Drops a table
     * @param string $table
     * @return bool
     * @throws ConnectionException
     */
    public function dropTable(string $table): bool
    {
        // DROP TABLE
        $sql = 'DROP TABLE ' . $table . ';';

        // execute
        return !!($this->execute($sql));
    }
}