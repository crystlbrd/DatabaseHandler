<?php


namespace crystlbrd\DatabaseHandler\Tests\Units\Parser;


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
            // get a placeholder
            $placeholder = $this->Parser->getPlaceholder(':param');

            // bind the value
            $this->Parser->bindValue($value, $placeholder);

            // create a copy to test against
            $boundValues[$placeholder] = $value;

            // the value has to be accessible
            self::assertSame($value, $this->Parser->getValueOf($placeholder));

            // the current count has to match the bound values
            self::assertCount($i, $this->Parser->getBoundValues());

            $i++;
        }

        // test the results
        self::assertIsArray($this->Parser->getBoundValues());
        self::assertSame($boundValues, $this->Parser->getBoundValues());

        // reset the bound values
        $this->Parser->resetPlaceholders();

        // there should not be any bound values
        self::assertIsArray($this->Parser->getBoundValues());
        self::assertCount(0, $this->Parser->getBoundValues());

        // the placeholder generation should start again at 0
        self::assertSame(':param0', $this->Parser->getPlaceholder(':param'));
    }
}