<?php


namespace crystlbrd\DatabaseHandler\Tests\Datasets\Parser;


use crystlbrd\DatabaseHandler\Interfaces\IConnection;

trait MySQL
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
}