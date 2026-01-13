<?php

namespace App\Contracts;

use App\DTOs\AIResponse;

interface AIProviderInterface
{
    public function chat(array $messages, array $options = []): AIResponse;
    public function getModel(): string;
    public function getMaxTokens(): int;
}