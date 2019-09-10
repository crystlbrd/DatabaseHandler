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
     * @throws DatabaseHandlerException
     * @author crystlbrd
     */
    public function testGetTableName()
    {
        $dataSet = [
            'table1',
            'table2'
        ];

        foreach ($dataSet as $tableName) {
            // load table
            $table = $this->DatabaseHandler->load($tableName);

            // check
            self::assertSame($tableName, $table->getTableName());
        }
    }

    /**
     * Tests the insert method
     * @throws DatabaseHandlerException
     * @author crystlbrd
     */
    public function testInsert()
    {
        // load table
        $table = $this->DatabaseHandler->load('table1');

        // insert data
        # TODO: read the current auto_increment value from the table to define the expected value dynamically
        $dataset = [
            [
                'data' => [
                    'col2' => 'test.123.1',
                    'col3' => 1
                ],
                'expected' => 6
            ],
            [
                'data' => [
                    'col2' => 'test.123.2',
                    'col3' => 0.12445
                ],
                'expected' => 7
            ]
        ];

        foreach ($dataset as $set) {
            self::assertSame($set['expected'], $table->insert($set['data']));
            # TODO: test, if rows are actually inserted into the database
        }
    }
}