<?php

namespace crystlbrd\DatabaseHandler\RowList;

use crystlbrd\DatabaseHandler\Exceptions\RowListException;
use crystlbrd\DatabaseHandler\IConnection;
use crystlbrd\DatabaseHandler\IRowList;
use crystlbrd\DatabaseHandler\Row;
use crystlbrd\Exceptionist\Environment;
use crystlbrd\Exceptionist\ExceptionistTrait;
use PDOStatement;

class PDORowList implements IRowList
{
    use ExceptionistTrait;


    /**
     * @var IConnection
     */
    private $Connection;

    /**
     * @var array
     */
    private $Result = [];

    /**
     * @var int
     */
    private $Pointer = 0;

    /**
     * IRowList constructor.
     * @param IConnection $connection current connection
     * @param mixed $data received results
     * @throws RowListException
     */
    public function __construct(IConnection $connection, $data)
    {
        // validate data
        if ($data instanceof PDOStatement) {
            // save connection
            $this->Connection = $connection;

            // parse data
            $this->parseStatement($data);
        } else {
            // given data is not valid
            $this->log(new RowListException('Invalid data type!'), Environment::E_LEVEL_ERROR);
        }
    }

    /**
     * Fetches all rows inside the statement and saves them internally as instances of Row
     * @param PDOStatement $stm
     */
    private function parseStatement(PDOStatement $stm)
    {
        while ($r = $stm->fetch(\PDO::FETCH_ASSOC)) {
            $this->Result[] = new Row($this->Connection, $r);
        }
    }

    /**
     * Fetches a row from the results
     * @return bool|Row
     */
    public function fetch()
    {
        // try to get the next row
        if (isset($this->Result[$this->Pointer])) {
            return $this->Result[$this->Pointer++];
        } else {
            // return false, if there is none
            return false;
        }
    }

    /**
     * Returns all rows from the result as a numeric array
     * @return array
     */
    public function fetchAll(): array
    {
        // TODO: Implement fetchAll() method.
    }

    /**
     * Returns the amount of rows in the result
     * @return int
     */
    public function countRows(): int
    {
        // TODO: Implement countRows() method.
    }

    /**
     * Sets the pointer again to the beginning
     */
    public function reset(): void
    {
        // TODO: Implement reset() method.
    }

    /**
     * Updates all rows
     * @param array $data array(column => new value)
     * @return bool
     */
    public function update(array $data): bool
    {
        // TODO: Implement update() method.
    }

    /**
     * Deletes all rows from the data source
     * @return bool
     */
    public function delete(): bool
    {
        // TODO: Implement delete() method.
    }
}