<?php

namespace crystlbrd\DatabaseHandler\Exceptions;

use \Exception;

class DatabaseHandlerException extends Exception
{
    const EXCP_CODE_CONNECTION_ALREADY_DEFINED = 1000;
    const EXCP_CODE_CONNECTION_NOT_DEFINED = 1001;

    const EXCP_CODE_INVALID_ARGUMENT = 1100;
    const EXCP_CODE_UNSUPPORTED_FEATURE = 1101;
}