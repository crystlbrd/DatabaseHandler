<?php


namespace crystlbrd\DatabaseHandler\Tests\Datasets\Parser;


use crystlbrd\DatabaseHandler\Interfaces\IConnection;

class MySQL
{
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

    public function validColumnSelectorsAndThereExpectedOutputs(): array
    {
        return [
            'empty selector' => [
                [],
                '*'
            ],
            'simple column selection' => [
                ['col1', 'col2', 'col3'],
                'col1, col2, col3'
            ],
            'AS syntax' => [
                ['col1' => 'a', 'col2' => 'b', 'col3' => 'c'],
                'col1 AS a, col2 AS b, col3 AS c'
            ],
            'with table name' => [
                ['table1.col1', 'table1.col2' => 'c', 'table2.col1'],
                'table1.col1, table1.col2 AS c, table2.col1'
            ]
        ];
    }

    public function validWhereConditionsAndThereExpectedOutputs(): array
    {
        return [
            'empty condition array' => [
                [],
                '',
                ''
            ],
            'simple AND-combined conditions (level 1 syntax)' => [
                [
                    'col1' => 'a',
                    'col2' => 2,
                    'col3' => null
                ],
                'col1 = "a" AND col2 = 2 AND col3 IS NULL',
                'col1 = :param0 AND col2 = 2 AND col3 IS NULL'
            ],
            'simple AND-combined conditions (level 2 syntax)' => [
                [
                    'and' => [
                        'col1' => 'a',
                        'col2' => 2,
                        'col3' => null
                    ]
                ],
                'col1 = "a" AND col2 = 2 AND col3 IS NULL',
                'col1 = :param0 AND col2 = 2 AND col3 IS NULL'
            ],
            'multiple AND-combined conditions on one column' => [
                [
                    'and' => [
                        'col1' => ['a', 1],
                        'col2' => null
                    ]
                ],
                'col1 = "a" AND col1 = 1 AND col2 IS NULL',
                'col1 = :param0 AND col1 = 1 AND col2 IS NULL',
            ],
            'simple OR-combined conditions' => [
                [
                    'or' => [
                        'col1' => 'a',
                        'col2' => 2,
                        'col3' => null
                    ]
                ],
                'col1 = "a" OR col2 = 2 OR col3 IS NULL',
                'col1 = :param0 OR col2 = 2 OR col3 IS NULL'
            ],
            'complex OR-combined conditions' => [
                [
                    'or' => [
                        'col1' => ['a', 1],
                        ['col2' => 'b', 'col3' => 3.1],
                        ['col4' => 'c', 'col5' => [-7, null]]
                    ]
                ],
                // value detection
                'col1 = "a" OR col1 = 1 ' .
                'OR col2 = "b" AND col3 = 3.1 ' .
                'OR col4 = "c" AND col5 = -7 AND col5 IS NULL',
                // placeholders
                'col1 = :param0 OR col1 = 1 ' .
                'OR col2 = :param1 AND col3 = 3.1 ' .
                'OR col4 = :param2 AND col5 = -7 AND col5 IS NULL'
            ],
            'simple combined conditions' => [
                [
                    'or' => [
                        'col1' => 'a',
                        'col2' => 2
                    ],
                    'and' => [
                        'col3' => 3.3,
                        'col4' => null
                    ]
                ],
                // value detection
                'col1 = "a" AND col3 = 3.3 AND col4 IS NULL ' .
                'OR col2 = 2 AND col3 = 3.3 AND col4 IS NULL',
                // placeholders
                'col1 = :param0 AND col3 = 3.3 AND col4 IS NULL ' .
                'OR col2 = 2 AND col3 = 3.3 AND col4 IS NULL',
            ],
            'complex combined conditions' => [
                [
                    'and' => [
                        'col1' => 'a',
                        'col2' => ['b', 2],
                    ],
                    'or' => [
                        'col3' => 'c',
                        'col4' => [4.1, 4.2],
                        ['col5' => 'd', 'col6' => 'e'],
                        ['col6' => null, 'col7' => [7, null]]
                    ]
                ],
                // value detection
                'col3 = "c" AND col1 = "a" AND col2 = "b" AND col2 = 2 ' .
                'OR col4 = 4.1 AND col1 = "a" AND col2 = "b" AND col2 = 2 ' .
                'OR col4 = 4.2 AND col1 = "a" AND col2 = "b" AND col2 = 2 ' .
                'OR col5 = "d" AND col6 = "e" AND col1 = "a" AND col2 = "b" AND col2 = 2 ' .
                'OR col6 IS NULL AND col7 = 7 AND col7 IS NULL AND col1 = "a" AND col2 = "b" AND col2 = 2',
                // placeholders
                'col3 = :param0 AND col1 = :param1 AND col2 = :param2 AND col2 = 2 ' .
                'OR col4 = 4.1 AND col1 = :param1 AND col2 = :param2 AND col2 = 2 ' .
                'OR col4 = 4.2 AND col1 = :param1 AND col2 = :param2 AND col2 = 2 ' .
                'OR col5 = :param3 AND col6 = :param4 AND col1 = :param1 AND col2 = :param2 AND col2 = 2 ' .
                'OR col6 IS NULL AND col7 = 7 AND col7 IS NULL AND col1 = :param1 AND col2 = :param2 AND col2 = 2',
            ]
        ];
    }

    public function validValuesWithExpectedOutputs(): array
    {
        return [
            'string (no syntax)' => [
                'hello world',
                ' = "hello world"',
                ' = :param0'
            ],
            'int (no syntax)' => [
                12345,
                ' = 12345',
                ' = 12345'
            ],
            'float (no syntax)' => [
                12.345,
                ' = 12.345',
                ' = 12.345'
            ],
            'null (no syntax)' => [
                null,
                ' IS NULL',
                ' IS NULL'
            ],
            'equals (simple syntax)' => [
                '= hello world!',
                ' = "hello world!"',
                ' = :param0'
            ],
            'greater than (simple syntax)' => [
                '> 2',
                ' > 2',
                ' > 2'
            ],
            'equal or greater than (simple syntax)' => [
                '>= 2',
                ' >= 2',
                ' >= 2'
            ],
            'lesser than (simple syntax)' => [
                '< 5.23',
                ' < 5.23',
                ' < 5.23'
            ],
            'equal or lesser than (simple syntax)' => [
                '<= 5.23',
                ' <= 5.23',
                ' <= 5.23'
            ],
            'not equal (simple syntax)' => [
                '!= hello world',
                ' != "hello world"',
                ' != :param0'
            ],
            'not null (simple syntax)' => [
                '! null',
                ' IS NOT NULL',
                ' IS NOT NULL'
            ],
            'like (simple syntax)' => [
                '~ %hello world%',
                ' LIKE "%hello world%"',
                ' LIKE :param0'
            ],
            'not like (simple syntax)' => [
                '!~ area51',
                ' NOT LIKE "area51"',
                ' NOT LIKE :param0'
            ],
            'equals (full syntax)' => [
                '={{> hello world! <}}',
                ' = "> hello world! <"',
                ' = :param0'
            ],
            'greater than (full syntax)' => [
                '>{{2}}',
                ' > 2',
                ' > 2'
            ],
            'equal or greater than (full syntax)' => [
                '>={{2}}',
                ' >= 2',
                ' >= 2'
            ],
            'lesser than (full syntax)' => [
                '<{{5.23}}',
                ' < 5.23',
                ' < 5.23'
            ],
            'equal or lesser than (full syntax)' => [
                '<={{5.23}}',
                ' <= 5.23',
                ' <= 5.23'
            ],
            'not equal (full syntax)' => [
                '!={{hello world}}',
                ' != "hello world"',
                ' != :param0'
            ],
            'not null (full syntax)' => [
                '!{{null}}',
                ' IS NOT NULL',
                ' IS NOT NULL'
            ],
            'like (full syntax)' => [
                '~{{%hello world%}}',
                ' LIKE "%hello world%"',
                ' LIKE :param0'
            ],
            'not like (full syntax)' => [
                '!~{{area51}}',
                ' NOT LIKE "area51"',
                ' NOT LIKE :param0'
            ]
        ];
    }

    public function validOptionsWithExpectedOutputs(): array
    {
        return [
            'empty array' => [
                [],
                ''
            ],
            'ORDER BY one column (without asc parameter)' => [
                [
                    'order' => 'col'
                ],
                ' ORDER BY col ASC'
            ],
            'ORDER BY one column (with asc parameter)' => [
                [
                    'order' => [
                        'col' => 'asc'
                    ]
                ],
                ' ORDER BY col ASC'
            ],
            'ORDER BY one column (desc)' => [
                [
                    'order' => [
                        'col' => 'desc'
                    ]
                ],
                ' ORDER BY col DESC'
            ],
            'ORDER BY multiple columns' => [
                [
                    'order' => [
                        'col1',
                        'col2' => 'asc',
                        'col3' => 'desc'
                    ]
                ],
                ' ORDER BY col1 ASC, col2 ASC, col3 DESC'
            ],
            'GROUP BY one column (string syntax)' => [
                ['group' => 'col'],
                ' GROUP BY col'
            ],
            'GROUP BY one column (array syntax)' => [
                [
                    'group' => [
                        'col'
                    ]
                ],
                ' GROUP BY col'
            ],
            'GROUP BY multiple columns' => [
                [
                    'group' => [
                        'col1',
                        'col2'
                    ]
                ],
                ' GROUP BY col1, col2'
            ],
            'LIMIT (int syntax)' => [
                [
                    'limit' => 5
                ],
                ' LIMIT 5'
            ],
            'LIMIT (string syntax)' => [
                [
                    'limit' => '1, 23'
                ],
                ' LIMIT 1, 23'
            ],
            'all combined' => [
                [
                    'limit' => 10,
                    'order' => [
                        'col1',
                        'col2' => 'ASC',
                        'col3' => 'DESC'
                    ],
                    'group' => [
                        'col1',
                        'col2'
                    ]
                ],
                ' GROUP BY col1, col2 ORDER BY col1 ASC, col2 ASC, col3 DESC LIMIT 10'
            ]
        ];
    }

    public function validSelectParametersAndExpectedOutputs(): array
    {
        return [
            'simple select' => [
                'table',
                [],
                [],
                [],
                'SELECT * FROM table;',
                'SELECT * FROM table;'
            ],
            'simple AND-combined conditions' => [
                ['table'],
                ['col1', 'col2'],
                [
                    'col1' => 'a',
                    'col2' => 2
                ],
                [],
                'SELECT col1, col2 FROM table WHERE col1 = "a" AND col2 = 2;',
                'SELECT col1, col2 FROM table WHERE col1 = :param0 AND col2 = 2;'
            ],
            'extended AND-combined conditions' => [
                ['table1'],
                ['table1.col1', 'table1.col2'],
                [
                    'and' => [
                        'table1.col1' => ['val1.1', 'val1.2'],
                        'table1.col2' => 'val2'
                    ]
                ],
                [],
                '',
                ''
            ],
            'simple OR-combined conditions' => [
                ['table1'],
                ['table1.col1', 'table1.col2'],
                [
                    'or' => [
                        'table1.col1' => 'val1',
                        'table1.col2' => 'val2'
                    ]
                ],
                [],
                '',
                ''
            ],
            'extended OR-combined conditions' => [
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
                '',
                ''
            ],
            'combined conditions' => [
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
                '',
                ''
            ],
            'with options' => [
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
                '',
                ''
            ]
        ];
    }
}