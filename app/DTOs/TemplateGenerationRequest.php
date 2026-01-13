<?php

namespace App\DTOs;

use App\Enums\TemplateType;

readonly class TemplateGenerationRequest
{
    public function __construct(
        public TemplateType $type,
        public ?string $context = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: TemplateType::from($data['template_type']),
            context: $data['context'] ?? null,
        );
    }
}