<?php

namespace crystlbrd\DatabaseHandler;

class Table
{
    private $Connection;

    private $TableName;

    public function __construct(IConnection $connection, string $table)
    {
        // Init
        $this->Connection = $connection;
        $this->TableName = $table;
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
        // TODO: Implement select
    }
}