<?php


namespace crystlbrd\DatabaseHandler\Tests\Helper\Iterator;


use Iterator;

class SelectSQLIterator extends SQLIterator
{
    public function __construct(string $tableColumnSeparator, string $columnAliasSeparator)
    {
        // set rules
        // structure: array(string label, array data(array tables, array columns, array conditions, array options, string expected_query)
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
            ],
            [
                'label' => 'simple AND conditions',
                'data' => [
                    ['table1'],
                    ['table1.col1', 'table1.col2'],
                    [
                        'and' => [
                            'table1.col1' => 'val1',
                            'table1.col2' => 'val2'
                        ]
                    ],
                    [],
                    'SELECT table1.col1 AS table1' . $tableColumnSeparator . 'col1' . $columnAliasSeparator . 'col1, ' .
                    'table1.col2 AS table1' . $tableColumnSeparator . 'col2' . $columnAliasSeparator . 'col2 ' .
                    'FROM table1 ' .
                    'WHERE table1.col1 = "val1" AND table1.col2 = "val2" ;'
                ]
            ],
            [
                'label' => 'complex AND conditions',
                'data' => [
                    ['table1'],
                    ['table1.col1', 'table1.col2'],
                    [
                        'and' => [
                            'table1.col1' => ['val1.1', 'val1.2'],
                            'table1.col2' => 'val2'
                        ]
                    ],
                    [],
                    'SELECT table1.col1 AS table1' . $tableColumnSeparator . 'col1' . $columnAliasSeparator . 'col1, ' .
                    'table1.col2 AS table1' . $tableColumnSeparator . 'col2' . $columnAliasSeparator . 'col2 ' .
                    'FROM table1 ' .
                    'WHERE table1.col1 = "val1.1" AND table1.col1 = "val1.2" AND table1.col2 = "val2" ;'
                ]
            ],
            [
                'label' => 'simple OR conditions',
                'data' => [
                    ['table1'],
                    ['table1.col1', 'table1.col2'],
                    [
                        'or' => [
                            'table1.col1' => 'val1',
                            'table1.col2' => 'val2'
                        ]
                    ],
                    [],
                    'SELECT table1.col1 AS table1' . $tableColumnSeparator . 'col1' . $columnAliasSeparator . 'col1, ' .
                    'table1.col2 AS table1' . $tableColumnSeparator . 'col2' . $columnAliasSeparator . 'col2 ' .
                    'FROM table1 ' .
                    'WHERE table1.col1 = "val1" OR table1.col2 = "val2" ;'
                ]
            ],
            [
                'label' => 'complex OR conditions',
                'data' => [
                    ['table1'],
                    ['table1.col1', 'table1.col2'],
                    [
                        'or' => [
                            'table1.col1' => ['val1.1', 'val1.2'],
                            ['table1.col1' => 'val2.1', 'table1.col2' => 'val2.1'],
                            ['table1.col1' => 'val3.1', 'table1.col2' => ['val3.2', 'val3.3']]
                        ]
                    ],
                    [],
                    'SELECT table1.col1 AS table1' . $tableColumnSeparator . 'col1' . $columnAliasSeparator . 'col1, ' .
                    'table1.col2 AS table1' . $tableColumnSeparator . 'col2' . $columnAliasSeparator . 'col2 ' .
                    'FROM table1 ' .
                    'WHERE table1.col1 = "val1.1" OR table1.col1 = "val1.2" ' .
                    'OR table1.col1 = "val2.1" AND table1.col2 = "val2.1" ' .
                    'OR table1.col1 = "val3.1" AND table1.col2 = "val3.2" AND table1.col2 = "val3.3" ;'
                ]
            ],
            [
                'label' => 'simple combined conditions',
                'data' => [
                    ['table1'],
                    ['table1.col1', 'table1.col2'],
                    [
                        'or' => [
                            'table1.col1' => 'val1.1',
                            'table1.col2' => 'val2.1'
                        ],
                        'and' => [
                            'table1.col1' => 'val1.2',
                            'table1.col2' => 'val2.2'
                        ]
                    ],
                    [],
                    'SELECT table1.col1 AS table1' . $tableColumnSeparator . 'col1' . $columnAliasSeparator . 'col1, ' .
                    'table1.col2 AS table1' . $tableColumnSeparator . 'col2' . $columnAliasSeparator . 'col2 ' .
                    'FROM table1 ' .
                    'WHERE table1.col1 = "val1.1" AND table1.col1 = "val1.2" AND table1.col2 = "val2.2" OR table1.col2 = "val2.1" AND table1.col1 = "val1.2" AND table1.col2 = "val2.2" ;'
                ]
            ],
            [
                'label' => 'complex combined conditions',
                'data' => [
                    ['table1'],
                    ['table1.col1', 'table1.col2'],
                    [
                        'and' => [
                            'table1.col1' => 'val1',
                            'table1.col2' => ['val2.1', 'val2.2'],
                        ],
                        'or' => [
                            'table1.col1' => 'val3',
                            'table1.col2' => ['val4.1', 'val4.2'],
                            ['table1.col1' => 'val5.1', 'table1.col2' => 'val5.2'],
                            ['table1.col1' => 'val6.1', 'table1.col2' => ['val6.2', 'val6.3']]
                        ]
                    ],
                    [],
                    'SELECT table1.col1 AS table1' . $tableColumnSeparator . 'col1' . $columnAliasSeparator . 'col1, ' .
                    'table1.col2 AS table1' . $tableColumnSeparator . 'col2' . $columnAliasSeparator . 'col2 ' .
                    'FROM table1 ' .
                    'WHERE table1.col1 = "val3" AND table1.col1 = "val1" AND table1.col2 = "val2.1" AND table1.col2 = "val2.2" ' .
                    'OR table1.col2 = "val4.1" AND table1.col1 = "val1" AND table1.col2 = "val2.1" AND table1.col2 = "val2.2" ' .
                    'OR table1.col2 = "val4.2" AND table1.col1 = "val1" AND table1.col2 = "val2.1" AND table1.col2 = "val2.2" ' .
                    'OR table1.col1 = "val5.1" AND table1.col2 = "val5.2" AND table1.col1 = "val1" AND table1.col2 = "val2.1" AND table1.col2 = "val2.2" ' .
                    'OR table1.col1 = "val6.1" AND table1.col2 = "val6.2" AND table1.col2 = "val6.3" AND table1.col1 = "val1" AND table1.col2 = "val2.1" AND table1.col2 = "val2.2" ;'
                ]
            ],
            [
                'label' => 'operator parsing',
                'data' => [
                    ['table1'],
                    ['table1.col1', 'table1.col2'],
                    [
                        'and' => [
                            // simple syntax
                            'table1.col1' => [
                                '!= val1.1',
                                '= val1.2',
                                '< val1.3',
                                '> val1.4',
                                '~ val1.5',
                                '!~ val1.6',
                                '<= val1.7',
                                '>= val1.8',
                                '! NULL'
                            ],
                            // complex syntax
                            'table1.col2' => [
                                '!={{val2.1}}',
                                '={{val2.2}}',
                                '<{{val2.3}}',
                                '>{{val2.4}}',
                                '~{{val2.5}}',
                                '!~{{val2.6}}',
                                '<={{val2.7}}',
                                '>={{val2.8}}',
                                '!{{NULL}}'
                            ]
                        ]
                    ],
                    [],
                    'SELECT table1.col1 AS table1' . $tableColumnSeparator . 'col1' . $columnAliasSeparator . 'col1, ' .
                    'table1.col2 AS table1' . $tableColumnSeparator . 'col2' . $columnAliasSeparator . 'col2 ' .
                    'FROM table1 ' .
                    'WHERE table1.col1 != "val1.1" ' .
                    'AND table1.col1 = "val1.2" ' .
                    'AND table1.col1 < "val1.3" ' .
                    'AND table1.col1 > "val1.4" ' .
                    'AND table1.col1 LIKE "val1.5" ' .
                    'AND table1.col1 NOT LIKE "val1.6" ' .
                    'AND table1.col1 <= "val1.7" ' .
                    'AND table1.col1 >= "val1.8" ' .
                    'AND table1.col1 IS NOT NULL ' .
                    // complex
                    'AND table1.col2 != "val2.1" ' .
                    'AND table1.col2 = "val2.2" ' .
                    'AND table1.col2 < "val2.3" ' .
                    'AND table1.col2 > "val2.4" ' .
                    'AND table1.col2 LIKE "val2.5" ' .
                    'AND table1.col2 NOT LIKE "val2.6" ' .
                    'AND table1.col2 <= "val2.7" ' .
                    'AND table1.col2 >= "val2.8" ' .
                    'AND table1.col2 IS NOT NULL ;'
                ]
            ],
            [
                'label' => 'options parsing',
                'data' => [
                    ['table1'],
                    ['table1.col1', 'table1.col2'],
                    [],
                    [
                        'group' => 'table1.col1',
                        'order' => [
                            'table1.col1' => 'asc',
                            'table1.col2' => 'desc'
                        ]
                    ],
                    'SELECT table1.col1 AS table1' . $tableColumnSeparator . 'col1' . $columnAliasSeparator . 'col1, ' .
                    'table1.col2 AS table1' . $tableColumnSeparator . 'col2' . $columnAliasSeparator . 'col2 ' .
                    'FROM table1 ' .
                    'GROUP BY table1.col1 ' .
                    'ORDER BY table1.col1, table1.col2 DESC;'
                ]
            ]
        ];
    }
}