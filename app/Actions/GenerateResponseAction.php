<?php

namespace App\Actions;

use App\Contracts\AIProviderInterface;
use App\Contracts\EmailHistoryRepositoryInterface;
use App\DTOs\EmailResponseRequest;
use App\DTOs\AIResponse;
use App\Enums\EmailOperation;
use App\Services\PromptService;

class GenerateResponseAction
{
    public function __construct(
        private AIProviderInterface $aiProvider,
        private EmailHistoryRepositoryInterface $historyRepository,
        private PromptService $promptService,
    ) {}

    public function execute(EmailResponseRequest $request): AIResponse
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->promptService->getSystemPrompt(EmailOperation::RESPONSE, $request->tone)
            ],
            [
                'role' => 'user',
                'content' => $this->promptService->formatResponsePrompt($request->originalEmail, $request->instructions)
            ]
        ];

        $response = $this->aiProvider->chat($messages, [
            'temperature' => $request->tone->getTemperature(),
        ]);

        if ($response->success) {
            $this->historyRepository->create([
                'operation' => EmailOperation::RESPONSE->value,
                'input' => json_encode($request),
                'output' => $response->content,
                'tone' => $request->tone->value,
                'ai_usage' => $response->usage,
                'metadata' => $response->metadata,
            ]);
        }

        return $response;
    }
}
