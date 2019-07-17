<?php

namespace crystlbrd\DatabaseHandler\Connections;

use crystlbrd\DatabaseHandler\Drivers\MysqlPdoDriver;
use crystlbrd\DatabaseHandler\IConnection;
use crystlbrd\Exceptionist\ExceptionistTrait;

class MySQLConnection implements IConnection
{
    // Exceptionist for exception and error handling
    use ExceptionistTrait;

    // PDO connection
    use PdoConnection;

    // MySQL driver
    use MysqlPdoDriver;
}