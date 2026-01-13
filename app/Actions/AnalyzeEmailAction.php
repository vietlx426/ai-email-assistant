<?php

namespace App\Actions;

use App\Contracts\AIProviderInterface;
use App\Contracts\EmailHistoryRepositoryInterface;
use App\Contracts\CacheServiceInterface;
use App\DTOs\EmailAnalysisRequest;
use App\DTOs\AIResponse;
use App\Enums\EmailOperation;
use App\Services\PromptService;

class AnalyzeEmailAction
{
    public function __construct(
        private AIProviderInterface $aiProvider,
        private EmailHistoryRepositoryInterface $historyRepository,
        private PromptService $promptService,
        private CacheServiceInterface $cache,
    ) {}

    public function execute(EmailAnalysisRequest $request): AIResponse
    {
        $cacheKey = 'analysis:' . md5($request->emailContent);

        $cached = $this->cache->get($cacheKey);
        if ($cached) {
            return AIResponse::success(
                content: $cached['content'],
                usage: ['cached' => true],
                metadata: ['from_cache' => true]
            );
        }

        $messages = [
            [
                'role' => 'system',
                'content' => $this->promptService->getSystemPrompt(EmailOperation::ANALYZE)
            ],
            [
                'role' => 'user',
                'content' => $this->promptService->formatAnalyzePrompt($request->emailContent)
            ]
        ];

        $response = $this->aiProvider->chat($messages);

        if ($response->success) {
            $this->cache->put($cacheKey, [
                'content' => $response->content,
                'timestamp' => now()->toDateTimeString(),
            ], 3600);

            $this->historyRepository->create([
                'operation' => EmailOperation::ANALYZE->value,
                'input' => $request->emailContent,
                'output' => $response->content,
                'ai_usage' => $response->usage,
                'metadata' => $response->metadata,
            ]);
        }

        return $response;
    }
}