<?php


namespace crystlbrd\DatabaseHandler\Tests\Helper\Iterator;


use Iterator;

class SQLIterator implements Iterator
{
    private $ExpectedTranslations = [];
    private $Pointer = 0;

    public function __construct(string $tableColumnSeparator, string $columnAliasSeparator)
    {
        // set rules
        $this->ExpectedTranslations = [
            [
                'label' => 'simple select',
                'data' => [
                    ['table1'],
                    ['table1.col1', 'table1.col2'],
                    [],
                    [],
                    'SELECT table1.col1 AS table1' . $tableColumnSeparator . 'col1' . $columnAliasSeparator . 'col1, ' .
                    'table1.col2 AS table1' . $tableColumnSeparator . 'col2' . $columnAliasSeparator . 'col2 ' .
                    'FROM table1;'
                ]
            ]
        ];
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->ExpectedTranslations[$this->Pointer]['data'];
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
        return ($this->Pointer < count($this->ExpectedTranslations));
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