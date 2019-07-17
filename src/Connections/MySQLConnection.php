<?php

namespace crystlbrd\DatabaseHandler\Connections;

use crystlbrd\DatabaseHandler\Drivers\MySQLPDODriver;
use crystlbrd\DatabaseHandler\IConnection;

class MySQLConnection implements IConnection
{
    // PDO Connection
    use PDOConnection;

    // MySQL driver
    use MySQLPDODriver;
}