<?php

namespace crystlbrd\DatabaseHandler\Connections;

use crystlbrd\DatabaseHandler\Exceptions\ConnectionException;
use crystlbrd\DatabaseHandler\Interfaces\IConnection;
use crystlbrd\Exceptionist\Environment;
use crystlbrd\Exceptionist\ExceptionistTrait;
use PDO;
use PDOException;
use PDOStatement;

abstract class PDOConnection implements IConnection
{
    use ExceptionistTrait;

    /**
     * @var string host
     */
    protected $Host;

    /**
     * @var string user name
     */
    protected $User;

    /**
     * @var string password
     */
    protected $Pass;

    /**
     * @var string database name
     */
    protected $Name;

    /**
     * @var array options
     */
    protected $Options = [];

    /**
     * @var PDO connection
     */
    protected $PDO;

    /**
     * @var array the last PDO error
     */
    protected $LastError = [];

    /**
     * @var array SQL query history
     */
    protected $History = [];

    /**
     * @var int Counter for setting the parameter names
     */
    protected $ParameterIndex = 1;

    /**
     * @var array Cached Parameters
     */
    protected $Parameters = [];

    /**
     * Used to separate the table name from the column name in the alias
     */
    public const COLUMN_SEPERATOR = '_crysbrd_dbh_pdoc_';

    /**
     * Used to separate the column name and the user defined alias
     */
    public const ALIAS_SEPERATOR = '_as_';


    /**
     * IConnection constructor.
     * @param string $host host
     * @param string $user user name
     * @param string $pass password
     * @param string $name database name
     * @param array $options additional options
     */
    public function __construct(string $host, string $user, string $pass, string $name, array $options = [])
    {
        // save parameters
        $this->Host = $host;
        $this->User = $user;
        $this->Pass = $pass;
        $this->Name = $name;

        // save options and defaults
        $this->Options = array_merge([
            'encoding' => 'utf8mb4',
            'port' => 3306,
            'reporting' => Environment::E_LEVEL_ERROR
        ], $options);
    }

    /**
     * Opens the connection
     * @return bool
     * @throws ConnectionException
     */
    public function openConnection(): bool
    {
        // Connection already opened?
        if ($this->PDO === null) {
            try {
                /// build dst

                // host
                $dst = 'mysql:host=' . $this->Host . ';';

                // db name
                $dst .= 'dbname=' . $this->Name . ';';

                // encoding
                $dst .= 'charset=' . $this->Options['encoding'] . ';';

                // port
                $dst .= 'port=' . $this->Options['port'] . ';';

                // open connection
                $this->PDO = new PDO($dst, $this->User, $this->Pass);

                // we are happy and returning true
                return true;
            } catch (PDOException $e) {
                $this->log(new ConnectionException('Failed to connect to database!', $e), Environment::E_LEVEL_ERROR);
                return false;
            }
        } else {
            // just log the info
            $this->log(new ConnectionException('Connection already opened.'), Environment::E_LEVEL_DEBUG);

            // but it's not an error, so return true
            return true;
        }
    }

    /**
     * Closes the connection
     * @return bool
     */
    public function closeConnection(): bool
    {
        // Just delete the PDO object
        $this->PDO = null;

        // and return a happy true
        return true;
    }

    /**
     * Sets the host name
     * @param string $host host name
     * @return IConnection self for chainability
     */
    public function setHost(string $host): IConnection
    {
        $this->Host = $host;
        return $this;
    }

    /**
     * Sets user name
     * @param string $user user name
     * @return IConnection self for chainability
     */
    public function setUser(string $user): IConnection
    {
        $this->User = $user;
        return $this;
    }

    /**
     * Sets password
     * @param string $password password
     * @return IConnection self for chainability
     */
    public function setPassword(string $password): IConnection
    {
        $this->Pass = $password;
        return $this;
    }

    /**
     * Sets database name
     * @param string $name database name
     * @return IConnection self for chainability
     */
    public function setName(string $name): IConnection
    {
        $this->Name = $name;
        return $this;
    }

    /**
     * Sets a option
     * @param string $option option index
     * @param mixed $value option value
     * @return IConnection self for chainability
     */
    public function setOption(string $option, $value): IConnection
    {
        $this->Options[$option] = $value;
        return $this;
    }

    /**
     * Gets the database credentials
     * @param string|null $index Set to get only one entry (host, user, pass, name)
     * @return array|string
     */
    public function getCredentials(string $index = null)
    {
        $credentials = [
            'host' => $this->Host,
            'user' => $this->User,
            'pass' => $this->Pass,
            'name' => $this->Name
        ];

        if ($index != null && isset($credentials[$index])) {
            return $credentials[$index];
        } else {
            return $credentials;
        }
    }

    /**
     * Gets the options
     * @param string|null $index
     * @return array|string|null
     */
    public function getOptions(string $index = null)
    {
        if ($index != null) {
            return (isset($this->Options[$index]) ? $this->Options[$index] : null);
        } else {
            return $this->Options;
        }
    }

    /**
     * Gets all executed SQL queries
     * @return array
     */
    public function getQueryHistory(): array
    {
        return $this->History;
    }

    /**
     * Gets last executed SQL query
     * @return string
     */
    public function getLastQuery(): string
    {
        return array_values(array_slice($this->History, -1))[0];
    }

    /**
     * Gets last database error
     * @return array
     */
    public function getLastError(): array
    {
        return $this->LastError;
    }

    /**
     * Gets last inserted ID
     * @return int
     */
    public function getLastInsertId(): int
    {
        $id = $this->PDO->lastInsertId();
        return intval($id);
    }

    /**
     * Bind a parameter to the internal cache
     * @param string|int|float $value Value of the parameter
     * @return string
     */
    protected function bindParam($value): string
    {
        // Value already defined?
        $index = array_search($value, $this->Parameters, true);
        if ($index !== false) {
            return $index;
        } else {
            // get the next parameter index and count up
            $index = ':param' . $this->ParameterIndex++;

            // save value
            $this->Parameters[$index] = $value;

            // return index
            return $index;
        }
    }

    /**
     * Parses the operator placeholders
     * @param string $operator
     * @return bool|string
     */
    protected function parseOperator(string $operator)
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

        return (isset($dict_operators[$operator]) ? $dict_operators[$operator] : false);
    }

    /**
     * Splits operator and actual value
     * @param string $value The value mixed with the operator
     * @return string
     */
    protected function parseValue(string $value): string
    {
        // Try to detect complex syntax
        $e = explode('{{', $value);
        if (count($e) == 2) {
            $op = $this->parseOperator(trim($e[0]));
            if ($op) {
                $val = str_replace(['{{', '}}'], '', $e[1]);
                return $op . $this->bindParam($val);
            }
        }

        // Try to detect simple syntax
        $e = explode(' ', $value);
        if (count($e) > 1) {
            $op = $this->parseOperator($e[0]);
            if ($op) {
                unset($e[0]);
                $val = implode(' ', $e);
                return $op . $this->bindParam($val);
            }
        }

        // No special syntax detected. Just return the plain value
        return ' = ' . $this->bindParam($value);
    }

    /**
     * Parses the joining type
     * @param int $type
     * @return string
     */
    protected function parseJoin(int $type): string
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
                return 'FULL OUTER JOIN';
                break;
            case IConnection::JOIN_CROSS:
                return 'CROSS JOIN';
                break;
            default:
                return 'JOIN';
                break;
        }
    }

    /**
     * Parses a table selection and returns valid SQL table selection
     * @param string|array $tables
     * @return string
     * @throws ConnectionException
     */
    protected function parseTables($tables): string
    {
        // define string
        $sql = '';

        // check type
        if (is_string($tables)) {
            // one single table
            $sql .= $tables;
        } else if (is_array($tables)) {
            // multiple joined tables
            foreach ($tables as $table => $data) {
                if (is_int($table) && is_string($data)) {
                    // MAIN TABLE
                    $sql .= $data;
                } else if (is_string($table) && is_array($data) && isset($data['join']) && isset($data['on'])) {
                    // JOIN TABLE
                    $sql .= ' ' . $this->parseJoin($data['join']) . ' ' . $table;

                    // ON
                    $sql .= ' ON ' . $table . '.' . $data['on'];
                } else {
                    // Malformed array
                    $this->log(new ConnectionException('Invalid array syntax.'), Environment::E_LEVEL_ERROR);
                }
            }
        } else {
            // unsupported type: throw exception
            $this->log(new ConnectionException('Expected string or array for $tables, something else given.'), Environment::E_LEVEL_ERROR);
        }

        // return string
        return $sql;
    }

    /**
     * Parses a column selection and returns a valid SQL column selection
     * @param array $columns
     * @return string
     */
    protected function parseColumns(array $columns): string
    {
        // define string
        $sql = '';

        if (!empty($columns)) {
            $i = 0;
            foreach ($columns as $column => $label) {
                // add trailing comma
                $sql .= ($i != 0 ? ', ' : '');

                // check for AS syntax
                if (is_int($column)) {
                    list($table, $column) = $this->parseColumn($label);
                    $label = $column;
                } else {
                    list($table, $column) = $this->parseColumn($column);
                }

                // Build SQL
                $sql .= $table . '.' . $column . ' AS ' . $table . self::COLUMN_SEPERATOR . $column . self::ALIAS_SEPERATOR . $label;

                $i++;
            }
        } else {
            // Empty column selection are not supported - there breaking the parsing
            $this->log(new ConnectionException('Empty column selections are not supported!'), Environment::E_LEVEL_ERROR);

            // return an empty string if exceptions are disabled
            return '';
        }

        // return string
        return $sql;
    }

    /**
     * Splits a column selector into the table and column name
     * @param string $selector
     * @return array
     */
    protected function parseColumn(string $selector): array
    {
        return explode('.', $selector);
    }

    /**
     * Parses a condition definition and returns a valid SQL conditions statement
     * @param array $conditions
     * @return string
     * @throws ConnectionException
     */
    protected function parseConditions(array $conditions): string
    {
        // define string
        $sql = '';

        if (isset($conditions['or'])) {
            $i = 0;
            foreach ($conditions['or'] as $column => $value) {
                // reset
                $appended = false;

                // append OR
                $sql .= ($i != 0 ? ' OR ' : '');

                if (is_string($column) && is_string($value)) {
                    // normal OR condition
                    $sql .= $column . $this->parseValue($value);
                } else if (is_int($column) && is_array($value)) {
                    // injected AND condition
                    $sql .= $this->parseAndConditions($value, true);
                } else if (is_string($column) && is_array($value)) {
                    // multiple OR conditions on one column
                    $ii = 0;
                    foreach ($value as $val) {
                        $sql .= ($ii != 0 ? ' OR ' : '') . $column . $this->parseValue($val);

                        // append all AND conditions if any defined
                        if (isset($conditions['and'])) {
                            $sql .= $this->parseAndConditions($conditions['and'], false);
                            $appended = true;
                        }

                        $ii++;
                    }
                }

                // append all AND conditions if any defined
                if (isset($conditions['and']) && $appended === false) {
                    $sql .= $this->parseAndConditions($conditions['and'], false);
                }

                $i++;
            }
        } else if (isset($conditions['and'])) {
            $sql .= $this->parseAndConditions($conditions['and']);
        } else {
            // no conditions defined: throw warning
            $this->log(new ConnectionException('No conditions found.'), Environment::E_LEVEL_WARNING);
        }

        // return string
        return $sql;
    }

    /**
     * Parses an array of AND conditions
     * @param array $conditions
     * @param bool $skipFirstAnd
     * @return string
     */
    protected function parseAndConditions(array $conditions, bool $skipFirstAnd = true): string
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
                    $sql .= $column . $this->parseValue($val);

                    // count up
                    $ii++;
                }
            } else {
                // one condition on a column
                $sql .= $column . $this->parseValue($value);
            }

            $i++;
        }

        // return string
        return $sql;
    }

    /**
     * Parses a option array and returns the corresponding SQL translation
     * @param array $options
     * @return string
     * @throws ConnectionException
     */
    protected function parseOptions(array $options): string
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
                $this->log(new ConnectionException('Option "order" expected to be array, something else given.'), Environment::E_LEVEL_ERROR);
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
     * Executes the internally prepared statement
     * @param string $sql
     * @return bool|PDOStatement
     * @throws ConnectionException
     */
    protected function execute(string $sql)
    {
        // Prepare a statement
        try {
            $stm = $this->PDO->prepare($sql);

            // Bind all parameters
            foreach ($this->Parameters as $index => $value) {
                if ($value == 'NULL') {
                    // special rule for NULL values
                    $stm->bindValue($index, null, PDO::PARAM_INT);
                } else {
                    $stm->bindValue($index, $value);
                }

                // Replace parameter placeholder for internal cache
                $sql = str_replace(' ' . $index . ' ', ($value == 'NULL' ? ' NULL ' : ' "' . $value . '" '), $sql);
            }

            // reset parameter cache
            $this->Parameters = [];
            $this->ParameterIndex = 1;

            // Save query to cache
            $this->History[] = trim($sql);

            // execute statement
            if ($stm->execute()) {
                return $stm;
            } else {
                // save the error
                $this->LastError = $stm->errorInfo();

                // return false
                return false;
            }
        } catch (PDOException $e) {
            $this->log(new ConnectionException('Failed to execute query!', $e), Environment::E_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * Sends a SQL query to the database
     * @param string $sql SQL query
     * @return mixed received response
     */
    public function query(string $sql)
    {
        // cache query
        $this->History[] = $sql;

        // execute query and return result
        $res = $this->PDO->query($sql);
        if ($res) {
            return $res;
        } else {
            // save error
            $this->LastError = $this->PDO->errorInfo();

            // return false
            return false;
        }
    }
}