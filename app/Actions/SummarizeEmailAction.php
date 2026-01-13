<?php

namespace App\Actions;

use App\Contracts\AIProviderInterface;
use App\Contracts\EmailHistoryRepositoryInterface;
use App\DTOs\EmailSummaryRequest;
use App\DTOs\AIResponse;
use App\Enums\EmailOperation;
use App\Services\PromptService;

class SummarizeEmailAction
{
    public function __construct(
        private AIProviderInterface $aiProvider,
        private EmailHistoryRepositoryInterface $historyRepository,
        private PromptService $promptService,
    ) {}

    public function execute(EmailSummaryRequest $request): AIResponse
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->promptService->getSystemPrompt(EmailOperation::SUMMARIZE)
            ],
            [
                'role' => 'user',
                'content' => $this->promptService->formatSummaryPrompt($request->emailThread)
            ]
        ];

        $response = $this->aiProvider->chat($messages);

        if ($response->success) {
            $this->historyRepository->create([
                'operation' => EmailOperation::SUMMARIZE->value,
                'input' => $request->emailThread,
                'output' => $response->content,
                'ai_usage' => $response->usage,
                'metadata' => $response->metadata,
            ]);
        }

        return $response;
    }
}