<?php

namespace App\DTOs;

readonly class AIResponse
{
    public function __construct(
        public bool $success,
        public ?string $content = null,
        public ?array $usage = null,
        public ?string $error = null,
        public ?array $metadata = null,
    ) {}

    public static function success(string $content, array $usage = [], array $metadata = []): self
    {
        return new self(
            success: true,
            content: $content,
            usage: $usage,
            metadata: $metadata,
        );
    }

    public static function failure(string $error): self
    {
        return new self(
            success: false,
            error: $error,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'success' => $this->success,
            'content' => $this->content,
            'usage' => $this->usage,
            'error' => $this->error,
            'metadata' => $this->metadata,
        ], fn($value) => $value !== null);
    }
}