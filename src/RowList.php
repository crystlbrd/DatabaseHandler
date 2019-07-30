<?php

namespace crystlbrd\DatabaseHandler;

use crystlbrd\DatabaseHandler\Exceptions\RowListException;
use crystlbrd\Exceptionist\Environment;
use crystlbrd\Exceptionist\ExceptionistTrait;
use PDO;
use PDOStatement;

class RowList
{
    use ExceptionistTrait;

    private $Connection;
    private $RowPointer = 0;
    private $Rows = [];

    public function __construct(IConnection $connection, PDOStatement $data)
    {
        // save connection for later use
        $this->Connection = $connection;

        // fetch all data and save them to an internal array
        while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
            $this->Rows[] = new Row($connection, $row);
        };
    }

    /**
     * Fetches a row from the list
     * @return bool|Row
     */
    public function fetch()
    {
        // check for the next entry and return it
        if (isset($this->Rows[$this->RowPointer])) return $this->Rows[$this->RowPointer++];

        // if there is none reset the pointer
        $this->reset();

        // and return false
        return false;
    }

    /**
     * Returns all rows from the list
     * @return array
     */
    public function fetchAll(): array
    {
        // reset the pointer
        $this->reset();

        // and return just all rows
        return $this->Rows;
    }

    /**
     * Returns the amount of rows inside the list
     * @return int
     */
    public function rowCount(): int
    {
        // count the rows and return the result
        return count($this->Rows);
    }

    /**
     * Resets the internal pointer to the beginning
     */
    public function reset(): void
    {
        // reset the pointer to 0
        $this->RowPointer = 0;
    }

    /**
     * Updates all rows in the list with the provided data
     * @param array $data Data formatted like [col => val, col => val]
     * @return bool
     * @throws RowListException
     */
    public function update(array $data): bool
    {
        // go throw all rows
        foreach ($this->Rows as $row) {
            // iterate throw the data
            foreach ($data as $col => $val) {
                // set in every row the selected columns to the defined value
                $row->$col = $val;
            }

            // try to update and throw an exception, if failing
            if (!$row->update()) {
                $this->log(new RowListException('Failed to update row #' . $row->pkvalue() . '!'), Environment::E_LEVEL_ERROR);
            }
        }

        // if all went fine return true
        return true;
    }

    /**
     * Deletes all rows from the list
     * @return bool
     * @throws RowListException
     */
    public function delete(): bool
    {
        // go throw all rows
        foreach ($this->Rows as $i => $row) {
            // try to delete the row
            if ($row->delte()) {
                // remove the deleted row from the internal array
                unset($this->Rows[$i]);
            } else {
                // if you failed throw an exception
                $this->log(new RowListException('Failed to update row #' . $row->pkvalue() . '!'), Environment::E_LEVEL_ERROR);
            }
        }

        // if all went fine return true
        return true;
    }
}