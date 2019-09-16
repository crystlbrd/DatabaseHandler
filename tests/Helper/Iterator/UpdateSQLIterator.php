<?php

namespace crystlbrd\DatabaseHandler\Tests\Helper\Iterator;

class UpdateSQLIterator extends SQLIterator
{
    public function __construct()
    {
        $this->ExpectedTranslations = [
            [
                'label' => 'simple update',
                'data' => [
                    'table1',
                    [
                        'col2' => 'random_data1',
                        'col3' => 0.234,
                    ],
                    [],
                    'UPDATE table1 SET col2 = "random_data1" , col3 = "0.234" ;'
                ]
            ],
            [
                'label' => 'conditioned update',
                'data' => [
                    'table2',
                    [
                        'col2' => 'random_data1',
                    ],
                    [
                        'and' => [
                            'ref_table1' => 1
                        ]
                    ],
                    'UPDATE table2 SET col2 = "random_data1" WHERE ref_table1 = "1" ;'
                ]
            ]
        ];
    }
}