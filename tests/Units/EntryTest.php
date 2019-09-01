<?php


namespace crystlbrd\DatabaseHandler\Tests\Units;


use crystlbrd\DatabaseHandler\DatabaseHandler;
use crystlbrd\DatabaseHandler\Tests\Helper\TestCases\DatabaseTestCase;

class EntryTest extends DatabaseTestCase
{
    /**
     * @var DatabaseHandler
     */
    protected $DatabaseHandler;

    protected function setUp(): void
    {
        parent::setUp();

        // init DatabaseHandler
        $this->DatabaseHandler = new DatabaseHandler();

        // add connection
        $this->DatabaseHandler->addConnection('default', $this->DefaultConnection);
    }

    public function testGet()
    {
        $dataSet = [
            'val1.1',
            'val1.2',
            'val1.3',
            'val1.4',
            'val1.5'
        ];

        // load table
        $table = $this->DatabaseHandler->load('table1');

        // select data
        $result = $table->select(['col2']);

        // fetch data and check value
        $i = 0;
        while ($entry = $result->fetch()) {
            self::assertSame($dataSet[$i], $entry->col2);

            // check for not existing values
            self::assertNull($entry->col1);
            $i++;
        }
    }

    /**
     * @author crystlbrd
     */
    public function testSet()
    {
        # TODO: Waiting for update()
        self::markTestIncomplete();
    }
}