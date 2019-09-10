<?php

namespace crystlbrd\DatabaseHandler\Tests\Helper\Iterator;

class InsertSQLIterator extends SQLIterator
{
    public function __construct()
    {
        $this->ExpectedTranslations = [
            [
                'label' => 'simple insert',
                'data' => [
                    'table1',
                    [
                        'col2' => 'random_data1',
                        'col3' => 0.234,
                    ],
                    'INSERT INTO table1 (col2, col3) VALUES ( "random_data1" , "0.234" );'
                ]
            ],
            [
                'label' => 'another insert',
                'data' => [
                    'table2',
                    [
                        'col2' => 'random_data1',
                    ],
                    'INSERT INTO table2 (col2) VALUES ( "random_data1" );'
                ]
            ]
        ];
    }
}