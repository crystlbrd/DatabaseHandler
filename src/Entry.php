<?php

namespace crystlbrd\DatabaseHandler;

use Countable;
use crystlbrd\DatabaseHandler\Exceptions\EntryException;
use Iterator;

class Entry implements Iterator, Countable
{
    /**
     * @var Table
     */
    protected $Table;

    /**
     * @var array
     */
    protected $Data = [];

    /**
     * @var array
     */
    protected $Alias = [];

    /**
     * @var int Pointer for the Iterator interface
     */
    protected $Pointer = 0;

    /**
     * @var array
     */
    protected $Changelist = [];

    /**
     * Entry constructor.
     * @param Table $table
     * @param array $data
     */
    public function __construct(Table $table, array $data = [])
    {
        // init
        $this->Table = $table;
        $this->Data = $data;

        // parse alias
        if (!empty($this->Data)) {
            foreach ($this->Data[$this->Table->getTableName()] as $column => $info) {
                // move data to 'self'
                $this->Data['self'][$column] = $info;

                if ($info['alias'] !== $column) {
                    $this->Alias[$info['alias']] = $column;
                }
            }
        }

        // unset table reference
        unset($this->Data[$this->Table->getTableName()]);
    }

    /**
     * Sets a property
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        // try to find the column with its alias
        if (isset($this->Alias[$name])) {
            $this->Changelist[$this->Alias[$name]] = $value;
        } // no definition found: just add it to the changelist as it is
        else {
            $this->Changelist[$name] = $value;
        }
    }

    /**
     * Gets a fetched property
     * @param $name
     * @return Entry|null
     */
    public function __get($name)
    {
        // try to find the column inside the primary table
        if (isset($this->Data['self'][$name])) {
            return $this->Data['self'][$name]['value'];
        } // try to find the column with its alias
        else if (isset($this->Alias[$name])) {
            return $this->Data[$this->Table->getTableName()][$this->Alias[$name]]['value'];
        } // check, if there is a connected table with that name
        else if (isset($this->Data[$name])) {
            return new Entry(new Table($this->Table->getConnection(), $name), [$name => $this->Data[$name]]);
        } // nothing found
        else {
            return null;
        }
    }

    /**
     * Inserts all provided data into the database
     * @return bool
     */
    public function insert(): bool
    {
        $result = $this->Table->insert($this->Changelist);
        if ($result) {
            $this->Data['self'][$this->Table->getPrimaryColumn()]['value'] = $result;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates the selected entry. Data is provided directly or via __set()
     * @param array $data
     * @return bool
     * @throws EntryException
     */
    public function update(array $data = []): bool
    {
        // check if data is defined, otherwise take the changelist
        if (empty($data)) {
            $data = $this->Changelist;
        } else {
            $data = array_merge($this->Changelist, $data);
        }

        // get the primary column
        $primaryColumn = $this->Table->getPrimaryColumn();

        // check for the primary value
        if ($primaryColumn && $this->$primaryColumn !== null) {
            // update
            if ($this->Table->update($data, ['and' => [$primaryColumn => $this->$primaryColumn]])) {
                // reset the changelist
                $this->Changelist = [];

                // return true
                return true;
            } else {
                // update failed
                return false;
            }
        } else {
            // primary column is not loaded. Condition can't be build.
            throw new EntryException('Failed to update entry! Value for primary column is missing.');
        }
    }

    /**
     * Deletes the entry from the data source
     * @return bool
     * @throws EntryException
     * @throws Exceptions\TableException
     */
    public function delete(): bool
    {
        // check, if the primary column is loaded
        $pkCol = $this->Table->getPrimaryColumn();
        if ($pkCol && $this->$pkCol != null) {
            return $this->Table->drop(['and' => [$pkCol => $this->$pkCol]]);
        } else {
            // primary column is not loaded. Deleting is not save
            throw new EntryException('Failed to delete entry! Value for primary column is missing.');
        }
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

    /* *** Countable Stubs *** */

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->Data);
    }
}