<?php

namespace crystlbrd\DatabaseHandler;

class Result
{
    protected $Table;

    protected $Data = [];

    protected $Pointer = 0;

    public function __construct(Table $table, array $result)
    {
        // init
        $this->Table = $table;
        $this->Data = $this->parseResult($result);
    }

    private function parseResult(array $data): array
    {
        foreach ($data as $row) {
            $this->Data[] = new Entry($this->Table, $row);
        }
    }

    public function fetch()
    {
        if (isset($this->Data[$this->Pointer])) {
            return $this->Data[$this->Pointer++];
        } else {
            return false;
        }
    }

    public function fetchAll()
    {
        return $this->Data;
    }

    public function rewind()
    {
        $this->Pointer = 0;
    }

    public function count()
    {
        return count($this->Data);
    }

    public function update(array $changes): bool
    {
        foreach ($this->Data as $row) {
            foreach ($changes as $column => $value) {
                $row->$column = $value;
            }

            if (!$row->update()) return false;
        }

        return true;
    }

    public function delete(): bool
    {
        foreach ($this->Data as $row) {
            if (!$row->delete()) return false;
        }

        return true;
    }
}