<?php

namespace App\Exceptions;

use Exception;

class RateLimitException extends Exception
{
    public function __construct(
        public int $retryAfter = 60,
        string $message = "Rate limit exceeded",
        int $code = 429,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}