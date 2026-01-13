<?php

namespace App\Actions;

use App\Contracts\AIProviderInterface;
use App\Contracts\EmailHistoryRepositoryInterface;
use App\DTOs\EmailDraftRequest;
use App\DTOs\AIResponse;
use App\Enums\EmailOperation;
use App\Services\PromptService;

class DraftEmailAction
{
    public function __construct(
        private AIProviderInterface $aiProvider,
        private EmailHistoryRepositoryInterface $historyRepository,
        private PromptService $promptService,
    ) {}

    public function execute(EmailDraftRequest $request): AIResponse
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->promptService->getSystemPrompt(EmailOperation::DRAFT, $request->tone)
            ],
            [
                'role' => 'user',
                'content' => $this->promptService->formatDraftPrompt($request->description, $request->context)
            ]
        ];

        $response = $this->aiProvider->chat($messages, [
            'temperature' => $request->tone->getTemperature(),
        ]);

        if ($response->success) {
            $this->historyRepository->create([
                'operation' => EmailOperation::DRAFT->value,
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
