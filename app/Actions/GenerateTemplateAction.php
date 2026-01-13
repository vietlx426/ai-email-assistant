<?php

namespace App\Actions;

use App\Contracts\AIProviderInterface;
use App\Contracts\EmailTemplateRepositoryInterface;
use App\DTOs\TemplateGenerationRequest;
use App\DTOs\AIResponse;
use App\Enums\EmailOperation;
use App\Services\PromptService;

class GenerateTemplateAction
{
    public function __construct(
        private AIProviderInterface $aiProvider,
        private EmailTemplateRepositoryInterface $templateRepository,
        private PromptService $promptService,
    ) {}

    public function execute(TemplateGenerationRequest $request): AIResponse
    {
        $existingTemplates = $this->templateRepository->findByType($request->type->value);

        if ($existingTemplates->isNotEmpty() && !$request->context) {
            $template = $existingTemplates->first();
            $this->templateRepository->incrementUsageCount($template->id);

            return AIResponse::success(
                content: $template->content,
                metadata: [
                    'template_id' => $template->id,
                    'from_cache' => true,
                ]
            );
        }

        $messages = [
            [
                'role' => 'system',
                'content' => $this->promptService->getSystemPrompt(EmailOperation::TEMPLATE)
            ],
            [
                'role' => 'user',
                'content' => $this->promptService->formatTemplatePrompt($request->type, $request->context)
            ]
        ];

        $response = $this->aiProvider->chat($messages);

        if ($response->success) {
            preg_match_all('/\[([^\]]+)\]/', $response->content, $matches);
            $placeholders = array_unique($matches[1]);

            $template = $this->templateRepository->create([
                'name' => $request->type->getDescription() . ($request->context ? ' - ' . substr($request->context, 0, 50) : ''),
                'type' => $request->type->value,
                'content' => $response->content,
                'placeholders' => $placeholders,
                'metadata' => array_merge($response->metadata ?? [], [
                    'context' => $request->context,
                ]),
            ]);

            $response = AIResponse::success(
                content: $response->content,
                usage: $response->usage,
                metadata: array_merge($response->metadata ?? [], [
                    'template_id' => $template->id,
                    'placeholders' => $placeholders,
                ])
            );
        }

        return $response;
    }
}