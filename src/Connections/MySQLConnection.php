<?php

namespace crystlbrd\DatabaseHandler\Connections;

use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
use crystlbrd\DatabaseHandler\IConnection;
use crystlbrd\DatabaseHandler\RowList;
use crystlbrd\Exceptionist\Environment;
use crystlbrd\Exceptionist\ExceptionistTrait;
use PDOException;

class MySQLConnection extends PDOConnection
{
    use ExceptionistTrait;

    /**
     * @var int Counter for setting the parameter names
     */
    protected $ParameterIndex = 1;

    /**
     * @var array Cached Parameters
     */
    protected $Parameters = [];

    protected function bindParam($value): string
    {
        // Value already defined?
        $index = array_search($value, $this->Parameters, true);
        if ($index !== false) {
            return $index;
        } else {
            // get the next parameter index
            $index = ':param' . $this->ParameterIndex;

            // count up
            $this->ParameterIndex++;

            // save value
            $this->Parameters[$index] = $value;

            // return index
            return $index;
        }
    }

    protected function parseOperator(string $operator)
    {
        $dict_operators = [
            '=' => ' = ',
            '>' => ' > ',
            '<' => ' < ',
            '>=' => ' >= ',
            '<=' => ' <= ',
            '~' => ' LIKE '
        ];

        return (isset($dict_operators[$operator]) ? $dict_operators[$operator] : false);
    }

    protected function parseValue($value)
    {
        // Try to detect simple syntax
        $e = explode(' ', $value);
        if (count($e) > 1) {
            $op = $this->parseOperator($e[0]);
            if ($op) {
                unset($e[0]);
                $val = implode(' ', $e);
                return $op . $this->bindParam($val);
            }
        }

        // Try to detect complex syntax
        $e = explode('{{', $value);
        if (count($e) > 1) {
            $op = $this->parseOperator($e[0]);
            if ($op) {
                $val = str_replace('}}', '', $e[1]);
                return $op . $this->bindParam($val);
            }
        }

        // No special syntax detected. Just return the plain value
        return ' = ' . $this->bindParam($value);
    }

    protected function parseJoin(int $type): string
    {
        switch ($type) {
            case IConnection::JOIN_INNER:
                return 'INNER JOIN';
                break;
            case IConnection::JOIN_LEFT:
                return 'LEFT JOIN';
                break;
            case IConnection::JOIN_RIGHT:
                return 'RIGHT JOIN';
                break;
            case IConnection::JOIN_FULL:
                return 'FULL OUTER JOIN';
                break;
            case IConnection::JOIN_CROSS:
                return 'CROSS JOIN';
                break;
            default:
                return 'JOIN';
                break;
        }
    }

    protected function parseTables($tables): string
    {
        // define string
        $sql = '';

        // check type
        if (is_string($tables)) {
            // one single table
            $sql .= $tables;
        } else if (is_array($tables)) {
            // multiple joined tables
            foreach ($tables as $table => $data) {
                if (is_int($table) && is_string($data)) {
                    // MAIN TABLE
                    $sql .= $data;
                } else if (is_string($table) && is_array($data) && isset($data['join']) && isset($data['on'])) {
                    // JOIN TABLE
                    $sql .= ' ' . $this->parseJoin($data['join']) . ' ' . $table;

                    // ON
                    $sql .= ' ON ' . $table . $data['on'];
                } else {
                    // Malformed array
                    $this->log(new ConnectionException('Invalid array syntax.'), Environment::E_LEVEL_ERROR);
                }
            }
        } else {
            // unsupported type: throw exception
            $this->log(new ConnectionException('Expected string or array for $tables, something else given.'), Environment::E_LEVEL_ERROR);
        }

        // return string
        return $sql;
    }

    protected function parseColumns(array $columns): string
    {
        // define string
        $sql = '';

        if (!empty($columns)) {
            $i = 0;
            foreach ($columns as $column => $label) {
                // add trailing comma
                $sql .= ($i != 0 ? ', ' : '');

                // check for AS syntax
                if (is_int($column)) {
                    $sql .= $label;
                } else {
                    $sql .= $column . ' AS ' . $label;
                }

                $i++;
            }
        } else {
            $sql .= '*';
        }

        // return string
        return $sql;
    }

    protected function parseConditions(array $conditions): string
    {
        // define string
        $sql = '';

        if (isset($conditions['or'])) {
            $i = 0;
            foreach ($conditions['or'] as $column => $value) {
                // append OR
                $sql .= ($i != 0 ? ' OR ' : '');

                if (is_string($column) && is_string($value)) {
                    // normal OR condition
                    $sql .= $column . $this->parseValue($value);
                } else if (is_int($column) && is_array($value)) {
                    // injected AND condition
                    $sql .= $this->parseAndConditions($value, true);
                } else if (is_string($column) && is_array($value)) {
                    // multiple OR conditions on one column
                    $ii = 0;
                    foreach ($value as $val) {
                        $sql .= ($ii != 0 ? ' OR ' : '') . $column . $this->parseValue($val);
                        $ii++;
                    }
                }

                // append all AND conditions if any defined
                if (isset($conditions['and'])) {
                    $sql .= $this->parseAndConditions($conditions['and'], false);
                }

                $i++;
            }
        } else if (isset($conditions['and'])) {
            $sql .= $this->parseAndConditions($conditions['and']);
        } else {
            // no conditions defined: throw warning
            $this->log(new ConnectionException('No conditions found.'), Environment::E_LEVEL_WARNING);
        }

        // return string
        return $sql;
    }

    protected function parseAndConditions(array $conditions, bool $skipFirstAnd = true): string
    {
        // define string
        $sql = '';

        $i = 0;
        foreach ($conditions as $column => $value) {
            // append AND
            $sql .= ($i == 0 && $skipFirstAnd ? '' : ' AND ');

            if (is_array($value)) {
                // multiple conditions on a column
                $ii = 0;
                foreach ($value as $val) {
                    // append AND
                    $sql .= ($ii != 0 ? ' AND ' : '');

                    // write condition
                    $sql .= $column . $this->parseValue($val);

                    // count up
                    $ii++;
                }
            } else {
                // one condition on a column
                $sql .= $column . $this->parseValue($value);
            }

            $i++;
        }

        // return string
        return $sql;
    }

    protected function parseOptions(array $options): string
    {
        // define string
        $sql = '';

        /// OPTIONS

        // Group by
        if (isset($options['group'])) {
            $sql .= ' GROUP BY ' . $options['group'];
        }

        // Order by
        if (isset($options['order'])) {
            // check if value is array
            if (is_array($options['order'])) {
                // set counter
                $i = 0;

                // parse every order condition
                foreach ($options['order'] as $column => $type) {
                    // add trailing comma
                    $sql .= ($i != 0 ? ', ' : '');

                    // the column
                    $sql .= $column;

                    // sorting type
                    $sql .= ($type == 'desc' ? ' DESC' : '');

                    // count up
                    $i++;
                }
            } else {
                // throw exception
                $this->log(new ConnectionException('Option "order" expected to be array, something else given.'), Environment::E_LEVEL_ERROR);
            }
        }

        // Limit
        if (isset($options['limit'])) {
            $sql .= ' LIMIT ' . $options['limit'];
        }

        // return string
        return $sql;
    }

    protected function execute(string $sql)
    {
        // Prepare a statement
        try {
            $stm = $this->PDO->prepare($sql);

            // Bind all parameters
            foreach ($this->Parameters as $index => $value) {
                $stm->bindValue($index, $value);

                // Replace parameter placeholder for internal cache
                $sql = str_replace($index, '"' . $value . '"', $sql);
            }

            // reset parameter cache
            $this->Parameters = [];
            $this->ParameterIndex = 1;

            // Save query to cache
            $this->History[] = $sql;

            // execute statement
            if ($stm->execute()) {
                return $stm;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $this->log(new ConnectionException('Failed to execute query!', $e), Environment::E_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * Sends a SQL query to the database
     * @param string $sql SQL query
     * @return mixed received response
     */
    public function query(string $sql)
    {
        // cache query
        $this->History[] = $sql;

        // execute query and return result
        return $this->PDO->query($sql);
    }

    /**
     * Selects rows from a table
     * @param mixed $tables one or more table names
     * @param array $columns columns to select
     * @param array $conditions conditions
     * @param array $options additional options
     * @return RowList
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
            $sql .= ' WHERE ' . $this->parseConditions($conditions);
        }

        // ADDITIONAL OPTIONS
        if (!empty($options)) {
            $sql .= ' ' . $this->parseOptions($options);
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