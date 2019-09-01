<?php


namespace crystlbrd\DatabaseHandler\Tests\Units;


use crystlbrd\DatabaseHandler\DatabaseHandler;
use crystlbrd\DatabaseHandler\Entry;
use crystlbrd\DatabaseHandler\Exceptions\DatabaseHandlerException;
use crystlbrd\DatabaseHandler\Result;
use crystlbrd\DatabaseHandler\Tests\Helper\TestCases\DatabaseTestCase;

class ResultTest extends DatabaseTestCase
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

    /**
     * @author crystlbrd
     * @param null $result
     * @return Result|null
     * @throws DatabaseHandlerException
     */
    public function testFetch($result = null)
    {
        // load table
        $table = $this->DatabaseHandler->load('table1');

        // select data
        if (!$result) {
            $result = $table->select();
        }

        // fetch and check iterations
        $i = 0;
        while ($entry = $result->fetch()) {
            self::assertInstanceOf(Entry::class, $entry);
            $i++;
        }

        self::assertSame(5, $i);
        return $result;
    }

    /**
     * @author crystlbrd
     * @throws DatabaseHandlerException
     */
    public function testFetchAll()
    {
        // load table
        $table = $this->DatabaseHandler->load('table1');

        // select data
        $result = $table->select();

        $array = $result->fetchAll();

        self::assertIsArray($array);
        self::assertCount(5, $array);
    }

    /**
     * @author crystlbrd
     * @depends testFetch
     */
    public function testRewind(Result $result)
    {
        // rewind
        $result->rewind();

        // test again a full fetch cycle
        $this->testFetch($result);
    }

    /**
     * @author crystlbrd
     * @throws DatabaseHandlerException
     */
    public function testCount()
    {
        // load table
        $table = $this->DatabaseHandler->load('table1');

        // select data
        $result = $table->select();

        // check
        self::assertSame(5, $result->count());
    }

    /**
     * @author crystlbrd
     */
    public function testUpdate()
    {
        # TODO
        self::markTestIncomplete();
    }

    /**
     * @author crystlbrd
     */
    public function testDelete()
    {
        # TODO
        self::markTestIncomplete();
    }
}