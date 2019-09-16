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

    public function testInsert()
    {
        // load table
        $table = $this->DatabaseHandler->load('table1');

        // select the current auto_increment value
        # TODO: make it better
        $sql = '
        SELECT `AUTO_INCREMENT`
        FROM  INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = "' . $_ENV['db_name'] . '"
        AND   TABLE_NAME   = "table1";';

        $res = $this->DatabaseHandler->getConnection('default')->query($sql);
        $r = $res->fetch();

        $auto_increment = $r[0];

        // create a new row
        $row = $table->createNewRow();

        // insert some data
        $row->col2 = 'some_data';
        $row->col3 = 0.234;

        // insert
        self::assertTrue($row->insert());
        // check the id
        self::assertSame(intval($auto_increment), $row->col1);

        # TODO: check, if the row is actually inserted into the database
        # TODO: test multiple inserts, not just one
    }

    public function testUpdate()
    {
        // load table
        $table = $this->DatabaseHandler->load('table1');

        // select an entry
        $result  = $table->select([], ['and' => ['col1' => 1]]);
        self::assertNotFalse($result);
        self::assertNotSame(0, $result->count());

        $entry = $result->fetch();
        self::assertNotFalse($entry);

        // check values
        self::assertSame('1', $entry->col1);
        self::assertSame('val1.1', $entry->col2);
        self::assertSame('0.1', $entry->col3);

        // update some data
        $entry->col2 = 'updated_value1';
        $entry->col3 = 1337;

        // update
        self::assertTrue($entry->update(), json_encode($this->DatabaseHandler->getActiveConnection()->getLastError()));

        // reload the entry and test the data again
        // select an entry
        $result  = $table->select([], ['and' => ['col1' => 1]]);
        self::assertNotFalse($result);
        self::assertNotSame(0, $result->count());

        $entry = $result->fetch();
        self::assertNotFalse($entry);

        // check values
        self::assertSame('1', $entry->col1);
        self::assertSame('updated_value1', $entry->col2);
        self::assertSame('1337', $entry->col3);

    }
}