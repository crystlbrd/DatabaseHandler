<?php


namespace crystlbrd\DatabaseHandler\Tests\Units;


use crystlbrd\DatabaseHandler\Connections\MySQLConnection;
use crystlbrd\DatabaseHandler\DatabaseHandler;
use crystlbrd\DatabaseHandler\Result;
use crystlbrd\DatabaseHandler\Table;
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
            'some value',
            'some more values',
        ];

        // load table
        $table = $this->DatabaseHandler->load('table_a');

        // select data
        $result = $table->select(['a_col1']);

        // fetch data and check value
        $i = 0;
        while ($entry = $result->fetch()) {
            self::assertSame($dataSet[$i], $entry->a_col1);

            // test count
            self::assertCount(1, $entry);

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
        $table = $this->DatabaseHandler->load('table_a');

        // select an entry
        $result = $table->select([], ['and' => ['a_id' => 1]]);
        self::assertNotFalse($result);
        self::assertNotSame(0, $result->count());

        $entry = $result->fetch();
        self::assertNotFalse($entry);

        // check values
        self::assertSame('1', $entry->a_id);
        self::assertSame('some value', $entry->a_col1);
        self::assertSame('42', $entry->a_col2);

        // update some data
        $entry->a_col1 = 'updated_value1';
        $entry->a_col2 = 1337;

        // update
        self::assertTrue($entry->update(), json_encode($this->DatabaseHandler->getActiveConnection()->getLastError()));

        // reload the entry and test the data again
        // select an entry
        $result = $table->select([], ['and' => ['a_id' => 1]]);
        self::assertNotFalse($result);
        self::assertNotSame(0, $result->count());

        $entry = $result->fetch();
        self::assertNotFalse($entry);

        // check values
        self::assertSame('1', $entry->a_id);
        self::assertSame('updated_value1', $entry->a_col1);
        self::assertSame('1337', $entry->a_col2);
    }

    public function testSelectWithAlias()
    {
        // load table
        $this->DatabaseHandler->use('default');
        $table = $this->DatabaseHandler->load('table_a');

        self::assertInstanceOf(Table::class, $table);

        // Load some data with aliased column names
        $res = $table->select(['a_col1' =>  'col1']);

        self::assertInstanceOf(Result::class, $res);
        self::assertGreaterThan(0, count($res));

        // fetch data and check value
        foreach ($res as $r) {
            self::assertNotEmpty($r->col1);
            self::assertSame($r->a_col1, $r->col1);
        }
    }
}