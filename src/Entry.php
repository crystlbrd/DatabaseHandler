<?php

namespace crystlbrd\DatabaseHandler;

use crystlbrd\DatabaseHandler\Exceptions\EntryException;

class Entry
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
     * @var array
     */
    protected $Changelist = [];

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

    public function delete(): bool
    {
        # TODO
    }
}