<?php


namespace crystlbrd\DatabaseHandler\Tests\Units;


use crystlbrd\DatabaseHandler\DatabaseHandler;
use crystlbrd\DatabaseHandler\Exceptions\DatabaseHandlerException;
use crystlbrd\DatabaseHandler\Result;
use crystlbrd\DatabaseHandler\Tests\Helper\TestCases\DatabaseTestCase;

class TableTest extends DatabaseTestCase
{
    /**
     * @var DatabaseHandler
     */
    protected $DatabaseHandler;

    public function setUp(): void
    {
        parent::setUp();

        // init DatabaseHandler
        $this->DatabaseHandler = new DatabaseHandler();

        // add connection
        self::assertTrue($this->DatabaseHandler->addConnection('mysql', $this->DefaultConnection));
    }

    /**
     * @throws DatabaseHandlerException
     * @author crystlbrd
     */
    public function testSelect()
    {
        // load table
        $table = $this->DatabaseHandler->load('table1');

        // select everything
        $result = $table->select();

        self::assertInstanceOf(Result::class, $result);
    }

    /**
     * @throws DatabaseHandlerException
     * @author crysltbrd
     */
    public function testGetAllColumns()
    {
        $dataSet = [
            'table1' => [
                'col1',
                'col2',
                'col3'
            ],
            'table2' => [
                'col1',
                'col2',
                'ref_table1'
            ]
        ];

        foreach ($dataSet as $tableName => $columns) {
            // load table
            $table = $this->DatabaseHandler->load($tableName);

            // check
            self::assertSame($columns, $table->getAllColumns());
        }
    }

    /**
     * @author crystlbrd
     * @throws DatabaseHandlerException
     */
    public function testGetTableName()
    {
        $dataSet = [
            'table1',
            'table2'
        ];

        foreach ($dataSet as $tableName ) {
            // load table
            $table = $this->DatabaseHandler->load($tableName);

            // check
            self::assertSame($tableName, $table->getTableName());
        }

    }
}