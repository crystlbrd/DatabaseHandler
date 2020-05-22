<?php


namespace crystlbrd\DatabaseHandler\SQLParser;


use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
use crystlbrd\DatabaseHandler\Exceptions\ParserException;
use crystlbrd\DatabaseHandler\Interfaces\IConnection;
use crystlbrd\DatabaseHandler\Interfaces\ISQLParser;

class MySQLParser implements ISQLParser
{
    /**
     * @var array Bound values (placeholder => value)
     */
    protected $BoundValues = [];

    /**
     * @var int Current placeholder index
     */
    private $PlaceholderIndex = 0;

    /**
     * @var string $PlaceholderTemplate Template for naming placeholders
     */
    private $PlaceholderTemplate = ':param';

    /**
     * Used to separate the table name from the column name in the alias
     */
    public const COLUMN_SEPARATOR = '__msqp__';

    /**
     * Used to separate the column name and the user defined alias
     */
    public const ALIAS_SEPARATOR = '__as__';


    public function bindValue($value, string $placeholder): void
    {
        $this->BoundValues[$placeholder] = $value;
    }

    /**
     * Checks for type of the value and adds quotes, if required
     * @param string $value
     * @return string
     */
    public function detectType(string $value): string
    {
        if (is_numeric($value)) {
            return $value;
        } else if ($value === null) {
            return 'NULL';
        } else {
            return '"' . $value . '"';
        }
    }

    /**
     * Translates the JOIN type int into the corresponding SQL part
     * @param int $type JOIN type defined in IConnection
     * @return string
     * @throws ParserException
     */
    public function getJoinType(int $type): string
    {
        switch ($type) {
            case IConnection::JOIN_INNER:
                return 'INNER JOIN';
                break;
            case IConnection::JOIN_LEFT:
                return 'LEFT JOIN';
                break;
            case IConnection::JOIN_RIGHT:
                return 'RIGHT JOIN';
                break;
            case IConnection::JOIN_FULL:
                return 'FULL JOIN';
                break;
            case IConnection::JOIN_CROSS:
                return 'CROSS JOIN';
                break;
            default:
                throw new ParserException('Invalid join type!', ParserException::EXCP_CODE_INVALID_ARGUMENT);
                break;
        }
    }

    /**
     * @inheritDoc
     */
    public function getPlaceholder(): string
    {
        return $this->PlaceholderTemplate . $this->PlaceholderIndex++;
    }

    /**
     * @inheritDoc
     */
    public function getPlaceholderTemplate(): string
    {
        return $this->PlaceholderTemplate;
    }

    /**
     * @inheritDoc
     */
    public function getValueOf(string $placeholder)
    {
        if (isset($this->BoundValues[$placeholder])) return $this->BoundValues[$placeholder];
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getValues(): array
    {
        return $this->BoundValues;
    }

    /**
     * Generates the condition query for the AND conditions
     * @param array $conditions AND conditions
     * @param bool $skipFirstAnd Skip the first AND?
     * @param bool $detectValueTypes Detect the value type and add quotes accordingly?
     * @return string AND condition string
     */
    public function generateAndConditions(array $conditions, bool $skipFirstAnd = true, bool $detectValueTypes = true): string
    {
        // define string
        $sql = '';

        $i = 0;
        foreach ($conditions as $column => $value) {
            // append AND
            $sql .= ($i == 0 && $skipFirstAnd ? '' : ' AND ');

            if (is_array($value)) {
                // multiple conditions on a column
                $ii = 0;
                foreach ($value as $val) {
                    // append AND
                    $sql .= ($ii != 0 ? ' AND ' : '');

                    // write condition
                    $sql .= $column . $this->parseValue($val, $detectValueTypes);

                    // count up
                    $ii++;
                }
            } else {
                // one condition on a column
                $sql .= $column . $this->parseValue($value, $detectValueTypes);
            }

            $i++;
        }

        // return string
        return $sql;
    }

    /**
     * Generates the column selection query (SELECT ...)
     * @param array $columns Columns to select
     * @return string Column selection query
     * @throws ParserException
     */
    public function generateColumnSelection(array $columns): string
    {
        // define string
        $sql = '';

        if (!empty($columns)) {
           $i = 0;
            foreach ($columns as $col => $alias) {
                $c = ($i ? ', ' : '');

                if (is_int($col)) {
                    $sql .= $c . $alias;
                } else if (is_string($col) && is_string($alias)) {
                    $sql .= $c . $col . ' AS ' . $alias;
                } else {
                    throw new ParserException('Invalid column selector!', ParserException::EXCP_CODE_INVALID_ARGUMENT);
                }

                $i++;
           }
        } else {
            $sql = '*';
        }

        // return string
        return $sql;
    }

    /**
     * Generates additional options
     * @param array $options Options
     * @return string Option query (LIMIT, ORDER BY, etc.)
     * @throws ParserException
     */
    public function generateOptions(array $options): string
    {
        // define string
        $sql = '';

        /// OPTIONS

        // Group by
        if (isset($options['group'])) {
            $sql .= ' GROUP BY ' . $options['group'];
        }

        // Order by
        if (isset($options['order'])) {
            // check if value is array
            if (is_array($options['order'])) {
                // set counter
                $i = 0;

                $sql .= ' ORDER BY ';

                // parse every order condition
                foreach ($options['order'] as $column => $type) {
                    // add trailing comma
                    $sql .= ($i != 0 ? ', ' : '');

                    // the column
                    $sql .= $column;

                    // sorting type
                    $sql .= ($type == 'desc' ? ' DESC' : '');

                    // count up
                    $i++;
                }
            } else {
                // throw exception
                throw new ParserException('Option "order" expected to be an array, ' . gettype($options['order']) . ' given', ParserException::EXCP_CODE_INVALID_ARGUMENT);
            }
        }

        // Limit
        if (isset($options['limit'])) {
            $sql .= ' LIMIT ' . $options['limit'];
        }

        // return string
        return $sql;
    }

    /**
     * Generates the table selection query (FROM ...)
     * @param string|array $tables Tables to select
     * @return string Table selection query
     * @throws ParserException
     */
    public function generateTableSelection($tables): string
    {
        // define string
        $sql = '';

        // check type
        if (empty($tables)) {
            throw new ParserException('Empty tables selector!', ParserException::EXCP_CODE_INVALID_ARGUMENT);
        } else if (is_string($tables)) {
            // one single table
            $sql .= $tables;
        } else if (is_array($tables)) {
            $i = 0;
            foreach ($tables as $key => $val) {
                if (is_int($key) && is_string($val)) {
                    // simple multi table selection
                    $sql .= ($i ? ', ' : '') . $val;
                } else if (is_string($key) && is_array($val)) {
                    // joining
                    $table = $key;
                    $sql .= ($i ? ', ' : '') . $table;

                    foreach ($val as $joinType => $joinTables) {
                        if (is_int($joinType) && is_array($joinTables)) {
                            foreach ($joinTables as $joinTable => $on) {
                                if (is_string($joinTable) && is_array($on)) {
                                    $sql .= ' ' . $this->getJoinType($joinType) . ' ' . $joinTable . ' ON ';

                                    $o = 0;
                                    foreach ($on as $aTableCol => $bTableCol) {
                                        if (is_string($aTableCol) && is_string($bTableCol)) {
                                            $sql .= ($o ? ', ' : '') . $joinTable . '.' . $aTableCol . ' = ' . $table . '.' . $bTableCol;
                                        } else {
                                            throw new ParserException('Invalid joining on syntax!', ParserException::EXCP_CODE_INVALID_ARGUMENT);
                                        }

                                        $o++;
                                    }
                                } else {
                                    throw new ParserException('Invalid joining table syntax!', ParserException::EXCP_CODE_INVALID_ARGUMENT);
                                }
                            }
                        } else {
                            throw new ParserException('Invalid joining syntax!', ParserException::EXCP_CODE_INVALID_ARGUMENT);
                        }
                    }
                }

                $i++;
            }
        } else {
            // unsupported type: throw exception
            throw new ParserException('Expected string or array for $tables, ' . gettype($tables) . ' given', ParserException::EXCP_CODE_INVALID_ARGUMENT);
        }

        // return string
        return $sql;
    }

    /**
     * Generates WHERE conditions query (WHERE ...)
     * @param array $where WHERE conditions
     * @param bool $detectValueType Detect the value type and add quotes accordingly?
     * @return string WHERE condition query
     * @throws ParserException
     */
    public function generateWhereConditions(array $where, bool $detectValueType = true, bool $usePlaceholders = false, string $placeholderTemplate = ':param'): string
    {
        // define string
        $sql = '';

        if (isset($where['or'])) {
            $i = 0;
            foreach ($where['or'] as $column => $value) {
                // reset
                $appended = false;

                // append OR
                $sql .= ($i != 0 ? ' OR ' : '');

                if (is_string($column) && is_string($value)) {
                    // normal OR condition
                    $sql .= $column . $this->parseValue($value, $detectValueType, $usePlaceholders, $placeholderTemplate);
                } else if (is_int($column) && is_array($value)) {
                    // injected AND condition
                    $sql .= $this->generateAndConditions($value, true, $detectValueType);
                } else if (is_string($column) && is_array($value)) {
                    // multiple OR conditions on one column
                    $ii = 0;
                    foreach ($value as $val) {
                        $sql .= ($ii != 0 ? ' OR ' : '') . $column . $this->parseValue($val, $detectValueType, $usePlaceholders, $placeholderTemplate);

                        // append all AND conditions if any defined
                        if (isset($where['and'])) {
                            $sql .= $this->generateAndConditions($where['and'], false, $detectValueType);
                            $appended = true;
                        }

                        $ii++;
                    }
                }

                // append all AND conditions if any defined
                if (isset($where['and']) && $appended === false) {
                    $sql .= $this->generateAndConditions($where['and'], false, $detectValueType);
                }

                $i++;
            }
        } else if (isset($where['and'])) {
            $sql .= $this->generateAndConditions($where['and'], true, $detectValueType);
        } else {
            // no conditions defined: throw warning
            throw new ParserException('Could not find any conditions', ParserException::EXCP_CODE_INVALID_ARGUMENT);
        }

        // return string
        return $sql;
    }

    /**
     * @inheritDoc
     */
    public function insert(string $table, array $data, bool $usePlaceholders = false): string
    {
        // TODO: Implement insert() method.
    }

    /**
     * Parses the column selector and returns table and column name separately
     * @param string $selector
     * @return false|string[] [table, column]
     */
    public function parseColumn(string $selector)
    {
        $e = explode('.', $selector);
        if (count($e) == 1) {
            return ['', $e[0]];
        } else {
            return $e;
        }
    }

    /**
     * Translates the parser operator to a SQL operator
     * @param string $operator Operator
     * @return string SQL operator (default "=")
     */
    public function parseOperator(string $operator): string
    {
        $dict_operators = [
            '!~' => ' NOT LIKE ',
            '>=' => ' >= ',
            '<=' => ' <= ',
            '=' => ' = ',
            '!=' => ' != ',
            '>' => ' > ',
            '<' => ' < ',
            '~' => ' LIKE ',
            '!' => ' IS NOT '
        ];

        return (isset($dict_operators[$operator]) ? $dict_operators[$operator] : ' = ');
    }

    /**
     * Checks for operators and returns the value with the according operator
     * @param string|mixed $value Value or value with operator syntax
     * @param bool $detectValueTypes Detect the value type and add quotes accordingly?
     * @return string
     */
    public function parseValue($value, bool $detectValueTypes = true, bool $usePlaceholder = false, string $placeholderTemplate = ':param'): string
    {
        $val = $value;

        // Try to detect complex syntax
        $e = explode('{{', $value);
        if (count($e) == 2) {
            $op = $this->parseOperator(trim($e[0]));
            if ($op) {
                $val = str_replace(['{{', '}}'], '', $e[1]);
            }
        }

        // Try to detect simple syntax
        $e = explode(' ', $value);
        if (count($e) > 1) {
            $op = $this->parseOperator($e[0]);
            if ($op) {
                unset($e[0]);
                $val = implode(' ', $e);
            }
        }

        // No special syntax detected. Just return the plain value
        if (!isset($op)) {
            $op = ' = ';
        }

        $val = ($detectValueTypes ? $this->detectType($val) : $val);
        $val = ($usePlaceholder ? $this->bindValue($val, $this->getPlaceholder()) : $val);

        return $op . $val;
    }

    /**
     * @inheritDoc
     */
    public function resetPlaceholders(): void
    {
        // reset the index
        $this->PlaceholderIndex = 0;

        // delete all previous placeholders
        $this->BoundValues = [];
    }

    /**
     * @inheritDoc
     * @throws ParserException
     */
    public function select(array $tables, array $columns = [], array $where = [], array $options = [], bool $usePlaceholders = false): string
    {
        // reset the placeholders
        if ($usePlaceholders) $this->resetPlaceholders();

        // SELECT
        $sql = 'SELECT ';

        // COLUMNS
        $sql .= $this->generateColumnSelection($columns);

        // FROM
        $sql .= ' FROM ' . $this->generateTableSelection($tables);

        // WHERE
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . $this->generateWhereConditions($conditions, !$usePlaceholders, $usePlaceholders) . ' ';
        }

        // ADDITIONAL OPTIONS
        if (!empty($options)) {
            $sql .= $this->generateOptions($options);
        }

        // adding semicolon to end
        $sql .= ';';

        return $sql;
    }

    /**
     * @inheritDoc
     */
    public function setPlaceholderTemplate(string $template): void
    {
        $this->PlaceholderTemplate = $template;
    }

    /**
     * @inheritDoc
     */
    public function update(string $table, array $data, array $where = [], bool $usePlaceholders = false): string
    {
        // TODO: Implement update() method.
    }
}