<?php

namespace crystlbrd\DatabaseHandler;

use Countable;
use Iterator;

class Result implements Iterator, Countable
{
    protected $Table;

    protected $Data = [];

    protected $Pointer = 0;

    /**
     * Result constructor.
     * @param Table $table
     * @param array $result
     */
    public function __construct(Table $table, array $result)
    {
        // init
        $this->Table = $table;
        $this->Data = $this->parseResult($result);
    }

    /**
     * Parses provided data and saves it internally for later use
     * @param array $data
     * @return array
     */
    private function parseResult(array $data): array
    {
        $result = [];
        foreach ($data as $row) {
            $result[] = new Entry($this->Table, $row);
        }
        return $result;
    }

    /**
     * Fetches an entry from the result
     * @return bool|mixed
     */
    public function fetch()
    {
        if ($this->valid()) {
            $res = $this->current();
            $this->next();
            return $res;
        } else {
            return false;
        }
    }

    /**
     * Fetches all entries from the result
     * @return array
     */
    public function fetchAll()
    {
        return $this->Data;
    }

    /**
     * Counts the amount of entries
     * @return int
     */
    public function count(): int
    {
        return count($this->Data);
    }

    /**
     * Updates all entries with the provided data
     * @param array $changes
     * @return bool
     */
    public function update(array $changes): bool
    {
        foreach ($this->Data as $row) {
            if (!$row->update($changes)) return false;
        }

        return true;
    }

    /**
     * Deletes all entries from the data source
     * @return bool
     */
    public function delete(): bool
    {
        foreach ($this->Data as $row) {
            if (!$row->delete()) return false;
        }

        return true;
    }

    /* *** Iterator Stubs *** */

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->Data[$this->Pointer];
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->Pointer++;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->Pointer;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return ($this->Pointer < $this->count());
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->Pointer = 0;
    }
}