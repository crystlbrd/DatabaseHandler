<?php


namespace crystlbrd\DatabaseHandler\Parser;


use crystlbrd\DatabaseHandler\Connections\PDOConnection;
use crystlbrd\DatabaseHandler\Exceptions\ParserException;
use crystlbrd\DatabaseHandler\Interfaces\IParser;
use crystlbrd\Exceptionist\ExceptionistTrait;
use PDO;
use PDOStatement;

class PDOParser implements IParser
{
    use ExceptionistTrait;

    /**
     * Translates the result of a data source into an array
     * @param PDOStatement $data
     * @return array
     * @throws ParserException
     */
    public static function parse($data): array
    {
        if ($data instanceof PDOStatement) {
            $result = [];

            // fetch a row
            while ($r = $data->fetch(PDO::FETCH_ASSOC)) {
                // parse all columns
                foreach ($r as $column => $value) {
                    // separate the table name
                    list($table, $columnString) = explode(PDOConnection::COLUMN_SEPERATOR, $column);

                    // separate column from alias
                    list($column, $alias) = explode(PDOConnection::ALIAS_SEPERATOR, $columnString);

                    // save result
                    $result[$table][$column] = [
                        'value' => $value,
                        'alias' => $alias
                    ];
                }
            }

            return $result;
        } else {
            throw new ParserException('Invalid data type!');
        }
    }
}