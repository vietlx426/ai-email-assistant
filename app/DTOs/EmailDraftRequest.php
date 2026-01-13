<?php

namespace App\DTOs;

use App\Enums\EmailTone;

readonly class EmailDraftRequest
{
    public function __construct(
        public string $description,
        public EmailTone $tone,
        public ?string $context = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            description: $data['description'],
            tone: EmailTone::from($data['tone'] ?? 'professional'),
            context: $data['context'] ?? null,
        );
    }
}