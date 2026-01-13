<?php

namespace App\DTOs;

readonly class EmailSummaryRequest
{
    public function __construct(
        public string $emailThread,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            emailThread: $data['email_thread'],
        );
    }
}
