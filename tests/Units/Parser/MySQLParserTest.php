<?php


namespace crystlbrd\DatabaseHandler\Tests\Units\Parser;


use crystlbrd\DatabaseHandler\Exceptions\ParserException;
use crystlbrd\DatabaseHandler\SQLParser\MySQLParser;
use crystlbrd\Values\Exceptions\InvalidArgumentException;
use crystlbrd\Values\Exceptions\UnsupportedFeatureException;
use crystlbrd\Values\NumVal;
use crystlbrd\Values\StrVal;
use PHPUnit\Framework\TestCase;

class MySQLParserTest extends TestCase
{
    protected $Parser;

    protected function setUp(): void
    {
        $this->Parser = new MySQLParser();
    }

    /**
     * Tests bind and reading bound values
     * @throws InvalidArgumentException
     * @throws UnsupportedFeatureException
     */
    public function testBindingAndReadingValues()
    {
        // bind different values and check, if they are accessible
        $dataset = [
            StrVal::random('abcdefghjiklmnopqrstuvwxyz', 128),
            StrVal::randomHex(),
            null,
            NumVal::random(124512354)
        ];

        // Counter, to test the progress
        $i = 1;

        // A copy of the desired output
        $boundValues = [];

        foreach ($dataset as $value) {
            // bind the value
            $placeholder = $this->Parser->bindValue($value);

            // create a copy to test against
            $boundValues[$placeholder] = $value;

            // the value has to be accessible
            self::assertSame($value, $this->Parser->getValueOf($placeholder));

            // the current count has to match the bound values
            self::assertCount($i, $this->Parser->getValues());

            $i++;
        }

        // test the results
        self::assertIsArray($this->Parser->getValues());
        self::assertSame($boundValues, $this->Parser->getValues());

        // reset the bound values
        $this->Parser->resetPlaceholders();

        // there should not be any bound values
        self::assertIsArray($this->Parser->getValues());
        self::assertCount(0, $this->Parser->getValues());

        // the placeholder generation should start again at 0
        self::assertSame(':param0', $this->Parser->getPlaceholder());
    }

    /**
     * Tests the table selection generation with valid input
     * @dataProvider \crystlbrd\DatabaseHandler\Tests\Datasets\Parser\MySQL::validTableSelectorsAndThereExpectedOutputs()
     * @param $selector
     * @param string $expectedOutput
     * @throws ParserException
     */
    public function testTableSelectionGeneration($selector, string $expectedOutput)
    {
        self::assertSame($expectedOutput, $this->Parser->generateTableSelection($selector));
    }

    /**
     * Tests the table selection generation with invalid input
     * @dataProvider \crystlbrd\DatabaseHandler\Tests\Datasets\Parser\MySQL::invalidTableSelectors()
     * @param $selector
     */
    public function testTableSelectionGenerationWithInvalidInput($selector)
    {
        // every selector should be invalid and throw an exception
        $this->expectException(ParserException::class);
        $this->Parser->generateTableSelection($selector);
    }

    /**
     * Tests column selection generation with valid input
     * @dataProvider \crystlbrd\DatabaseHandler\Tests\Datasets\Parser\MySQL::validColumnSelectorsAndThereExpectedOutputs()
     * @param array $columnsSelector
     * @param string $expectedOutput
     * @throws ParserException
     */
    public function testColumnSelectionGeneration(array $columnsSelector, string $expectedOutput)
    {
        self::assertSame($expectedOutput, $this->Parser->generateColumnSelection($columnsSelector));
    }

    /**
     * Tests WHERE conditions building
     * @dataProvider \crystlbrd\DatabaseHandler\Tests\Datasets\Parser\MySQL::validWhereConditionsAndThereExpectedOutputs()
     * @param array $conditions
     * @param string $expectedOutputWithValueDetection
     * @param $expectedOutputWithPlaceholders
     */
    public function testWhereConditionGeneration(array $conditions, string $expectedOutputWithValueDetection, string $expectedOutputWithPlaceholders)
    {
        self::assertSame($expectedOutputWithValueDetection, $this->Parser->generateWhereConditions($conditions, false));
        self::assertSame($expectedOutputWithPlaceholders, $this->Parser->generateWhereConditions($conditions, true));
    }

    /**
     * Tests operation generation
     * @dataProvider \crystlbrd\DatabaseHandler\Tests\Datasets\Parser\MySQL::validOptionsWithExpectedOutputs()
     * @param array $options
     * @param string $expectedOutput
     * @throws ParserException
     */
    public function testOptionGeneration(array $options, string $expectedOutput)
    {
        self::assertSame($expectedOutput, $this->Parser->generateOptions($options));
    }

    public function testSelect()
    {
        # todo
        self::markTestIncomplete();
    }

    /**
     * Tests value and operator parsing
     * @dataProvider \crystlbrd\DatabaseHandler\Tests\Datasets\Parser\MySQL::validValuesWithExpectedOutputs()
     * @param $value
     * @param $expectedOutputWithValueDetection
     * @param $expectedOutputWithPlaceholders
     */
    public function testValueParsing($value, $expectedOutputWithValueDetection, $expectedOutputWithPlaceholders)
    {
        self::assertSame($expectedOutputWithValueDetection, $this->Parser->parseValue($value, false));
        self::assertSame($expectedOutputWithPlaceholders, $this->Parser->parseValue($value, true));
    }
}