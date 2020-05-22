<?php


namespace crystlbrd\DatabaseHandler\Tests\Units\Parser;


use crystlbrd\DatabaseHandler\Entry;
use crystlbrd\DatabaseHandler\Exceptions\ParserException;
use crystlbrd\DatabaseHandler\Interfaces\IConnection;
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
            $placeholder = $this->Parser->getPlaceholder();

            // bind the value
            $this->Parser->bindValue($value, $placeholder);

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

    public function validTableSelectorsAndThereExpectedOutputs()
    {
        return [
            'simple single table selection (as string)' => [
                'table1',
                'table1'
            ],
            'simple single table selection (as array)' => [
                ['table1'],
                'table1'
            ],
            'selecting from 2 tables' => [
                ['table1', 'table2'],
                'table1, table2'
            ],
            'selecting from even more tables' => [
                ['table1', 'table2', 'table3', 'table4', 'table5'],
                'table1, table2, table3, table4, table5'
            ],
            'joining a table on another' => [
                [
                    'table1' => [
                        IConnection::JOIN_INNER => [
                            'table2' => [
                                'ref_table1' => 'table1_col'
                            ]
                        ]
                    ]
                ],
                'table1 INNER JOIN table2 ON table2.ref_table1 = table1.table1_col'
            ],
            'all joining types' => [
                [
                    'table1' => [
                        IConnection::JOIN_INNER => [
                            'table2' => [
                                'ref_table1' => 'table1_col'
                            ]
                        ],
                        IConnection::JOIN_LEFT => [
                            'table3' => [
                                'ref_table1' => 'table1_col'
                            ]
                        ],
                        IConnection::JOIN_RIGHT => [
                            'table4' => [
                                'ref_table1' => 'table1_col'
                            ]
                        ],
                        IConnection::JOIN_FULL => [
                            'table5' => [
                                'ref_table1' => 'table1_col'
                            ]
                        ],
                        IConnection::JOIN_CROSS => [
                            'table6' => [
                                'ref_table1' => 'table1_col'
                            ]
                        ]
                    ]
                ],
                'table1 INNER JOIN table2 ON table2.ref_table1 = table1.table1_col ' .
                'LEFT JOIN table3 ON table3.ref_table1 = table1.table1_col ' .
                'RIGHT JOIN table4 ON table4.ref_table1 = table1.table1_col ' .
                'FULL JOIN table5 ON table5.ref_table1 = table1.table1_col ' .
                'CROSS JOIN table6 ON table6.ref_table1 = table1.table1_col'
            ],
            'selecting multiple tables, joining on one of them' => [
                [
                    'table1',
                    'table2' => [
                        IConnection::JOIN_INNER => [
                            'table3' => [
                                'ref_table2' => 'table2_col'
                            ]
                        ]
                    ]
                ],
                'table1, table2 INNER JOIN table3 ON table3.ref_table2 = table2.table2_col'
            ]
        ];
    }

    public function invalidTableSelectors(): array
    {
        return [
            'empty selector (string)' => [
                ''
            ],
            'empty selector (array)' => [
                []
            ],
            'some other data type than an string or array' => [
                5
            ],
            'invalid join syntax: using a string as the joining type' => [
                ['table1' => [
                    'inner' => [
                        'table2' => [
                            'col' => 'col'
                        ]
                    ]
                ]]
            ],
            'invalid join syntax: using an undefined join type' => [
                ['table1' => [
                    3748 => [
                        'table2' => [
                            'col' => 'col'
                        ]
                    ]
                ]]
            ]
            # todo: Well, there are definitely more invalid syntax ..
        ];
    }

    /**
     * Tests the table selection generation with valid input
     * @dataProvider validTableSelectorsAndThereExpectedOutputs
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
     * @dataProvider invalidTableSelectors
     * @param $selector
     */
    public function testTableSelectionGenerationWithInvalidInput($selector)
    {
        // every selector should be invalid and throw an exception
        $this->expectException(ParserException::class);
        $this->Parser->generateTableSelection($selector);
    }
}