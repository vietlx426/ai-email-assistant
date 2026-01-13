<?php

namespace App\DTOs;

readonly class EmailAnalysisRequest
{
    public function __construct(
        public string $emailContent,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            emailContent: $data['email_content'],
        );
    }
}