<?php

namespace crystlbrd\DatabaseHandler;

use crystlbrd\DatabaseHandler\Interfaces\IConnection;

class Table
{
    /**
     * @var IConnection
     */
    private $Connection;

    private $TableName;

    private $TableDefinition = [];

    private $TablePrimaryColumn;

    private $TableColumns = [];

    public function __construct(IConnection $connection, string $table)
    {
        // Init
        $this->Connection = $connection;
        $this->TableName = $table;
    }

    private function loadTableDefinition()
    {
        $this->TableDefinition = $this->Connection->describe($this->TableName);
    }

    private function loadTableColumns()
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

    public function select(array $columns = [], array $conditions = [], array $options = []): RowList
    {
        $result = $this->Connection->select($this->getTables(), $this->parseColumns($columns), $conditions, $options);
        return new RowList($this, $result);
    }

    private function getTables(): string
    {
        return $this->TableName;
    }

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
    }

    public function getAllColumns()
    {
        // load column definition if not already done
        if (empty($this->TableColumns)) {
            $this->loadTableColumns();
        }

        return $this->TableColumns;
    }
}