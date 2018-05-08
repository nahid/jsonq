<?php

namespace Nahid\JsonQ\Exceptions;

class FileNotFoundException extends \Exception
{
    public function __construct($message = "File not found exception", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
