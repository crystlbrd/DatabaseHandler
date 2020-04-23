<?php

namespace crystlbrd\DatabaseHandler\Exceptions;

class ConnectionException extends DatabaseHandlerException
{
    const EXCP_CODE_CONNECTION_FAILED = 2000;

    const EXCP_CODE_QUERY_EXECUTION_FAILED = 2100;
    const EXCP_CODE_PDO_ERROR = 2101;
}