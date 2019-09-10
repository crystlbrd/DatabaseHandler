<?php

namespace crystlbrd\DatabaseHandler;

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
                if ($info['alias'] !== $column) {
                    $this->Alias[$info['alias']] = $column;
                }
            }
        }
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
        if (isset($this->Data[$this->Table->getTableName()][$name])) {
            return $this->Data[$this->Table->getTableName()][$name]['value'];
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
            $this->Data[$this->Table->getTableName()][$this->Table->getPrimaryColumn()]['value'] = $result;
            return true;
        } else {
            return false;
        }
    }

    public function update(): bool
    {
        # TODO [Iss3]
    }

    public function delete(): bool
    {
        # TODO [Iss3]
    }
}