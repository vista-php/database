<?php

namespace Vista\Exceptions;

use Exception;

class ColumnNotFoundException extends Exception
{
    public function __construct(string $column)
    {
        parent::__construct("Column '$column' not found in the table.");
    }
}
