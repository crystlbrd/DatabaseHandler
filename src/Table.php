<?php

namespace crystlbrd\DatabaseHandler;

use crystlbrd\DatabaseHandler\Interfaces\IConnection;

class Table
{
    /**
     * @var IConnection selected connection
     */
    private $Connection;

    /**
     * @var string table name
     */
    private $TableName;

    /**
     * @var array table structure
     */
    private $TableDefinition = [];

    /**
     * @var string primary column name
     */
    private $TablePrimaryColumn;

    /**
     * @var array a list of the column names
     */
    private $TableColumns = [];

    /**
     * Table constructor.
     * @param IConnection $connection connection to use
     * @param string $table table name
     */
    public function __construct(IConnection $connection, string $table)
    {
        // Init
        $this->Connection = $connection;
        $this->TableName = $table;
    }

    /**
     * gets the table structure from the connection
     */
    private function loadTableDefinition(): void
    {
        $this->TableDefinition = $this->Connection->describe($this->TableName);
    }

    /**
     * Extracts the column name out of the structure definition
     */
    private function loadTableColumns(): void
    {
        // load table definition, if not already done
        if (empty($this->TableDefinition)) {
            $this->loadTableDefinition();
        }

        // extract the column names
        foreach ($this->TableDefinition as $column => $description) {
            if ($description['key'] == 'PRI') {
                $this->TablePrimaryColumn = $column;
            }

            $this->TableColumns[] = $column;
        }
    }

    public function autoJoin(): bool
    {
        // TODO: Implement autojoin()
    }

    public function join($table, int $joinType = IConnection::JOIN_INNER): bool
    {
        // TODO: Implement join()
    }

    /**
     * Selects data from the connection
     * @param array $columns
     * @param array $conditions
     * @param array $options
     * @return Result
     */
    public function select(array $columns = [], array $conditions = [], array $options = []): Result
    {
        $result = $this->Connection->select($this->getTables(), $this->parseColumns($columns), $conditions, $options);
        return new Result($this, $result);
    }

    /**
     * Gets all tables name, even the connected
     * @return string
     */
    private function getTables(): string
    {
        return $this->TableName;
    }

    /**
     * Prepares and unifies the column identifier
     * @param array $columns
     * @return array
     */
    private function parseColumns(array $columns): array
    {
        $result = [];

        // Select all columns if none defined
        if (empty($columns)) $columns = $this->getAllColumns();

        foreach ($columns as $column => $alias) {
            // Check for alias (AS) syntax
            if (is_int($column)) {
                $index = $column;
                $column = $alias;
            } else {
                $index = $column;
            }

            // Check if it has a table name in it
            if (count(explode('.', $column)) < 2) {
                // if not add the main table name to it
                // this is required for the connection to determine which table that column is attached to
                $column = implode('.', [$this->TableName, $column]);
            }

            $result[$column] = $alias;
        }

        return $result;
    }

    /**
     * Gets the column list
     * @return array
     */
    public function getAllColumns(): array
    {
        // load column definition if not already done
        if (empty($this->TableColumns)) {
            $this->loadTableColumns();
        }

        return $this->TableColumns;
    }

    /**
     * Gets the table name
     * @return string
     */
    public function getTableName(): string
    {
        return $this->TableName;
    }

    /**
     * Gets the connection
     * @return IConnection
     */
    public function getConnection(): IConnection
    {
        return $this->Connection;
    }
}