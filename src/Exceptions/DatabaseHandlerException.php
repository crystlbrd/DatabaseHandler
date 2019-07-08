<?php

namespace crystlbrd\DatabaseHandler\Exceptions;

use \Exception;
use Throwable;

class DatabaseHandlerException extends Exception
{
    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, $this->getInternalCode(), $previous);
    }

    protected function getInternalCode()
    {
        # TODO
        return 0;
    }
}