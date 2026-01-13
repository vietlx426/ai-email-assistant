<?php

namespace App\DTOs;

use App\Enums\EmailTone;

readonly class EmailResponseRequest
{
    public function __construct(
        public string $originalEmail,
        public EmailTone $tone,
        public ?string $instructions = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            originalEmail: $data['original_email'],
            tone: EmailTone::from($data['tone'] ?? 'professional'),
            instructions: $data['instructions'] ?? null,
        );
    }
}