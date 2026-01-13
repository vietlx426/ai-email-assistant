<?php

namespace App\Exceptions;

use Exception;

class AIServiceException extends Exception
{
    public function __construct(string $message = "AI service error", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}