<?php

namespace App\Services;

use App\Enums\EmailOperation;
use App\Enums\EmailTone;
use App\Enums\TemplateType;

class PromptService
{
    public function getSystemPrompt(EmailOperation $operation, ?EmailTone $tone = null): string
    {
        $basePrompts = [
            EmailOperation::DRAFT->value => $this->getDraftPrompt($tone),
            EmailOperation::RESPONSE->value => $this->getResponsePrompt($tone),
            EmailOperation::ANALYZE->value => $this->getAnalyzePrompt(),
            EmailOperation::SUMMARIZE->value => $this->getSummarizePrompt(),
            EmailOperation::TEMPLATE->value => $this->getTemplatePrompt(),
        ];

        return $basePrompts[$operation->value] ?? $basePrompts[EmailOperation::DRAFT->value];
    }

    protected function getDraftPrompt(?EmailTone $tone): string
    {
        $toneDesc = $tone?->getDescription() ?? 'professional';

        return "You are a professional email writing assistant. Write {$toneDesc} emails that are clear, " .
            "well-structured, and appropriate for business communication. Ensure proper formatting with " .
            "appropriate greetings and closings. Keep the language natural and engaging.";
    }

    protected function getResponsePrompt(?EmailTone $tone): string
    {
        $toneDesc = $tone?->getDescription() ?? 'professional';

        return "You are an email response assistant. Generate {$toneDesc} replies that address all points " .
            "in the original email while maintaining appropriate context. Ensure the response is relevant, " .
            "complete, and maintains the conversation flow naturally.";
    }

    protected function getAnalyzePrompt(): string
    {
        return "You are an email analyzer. Analyze emails for tone, clarity, professionalism, and effectiveness. " .
            "Provide specific, actionable feedback. Be constructive and helpful in your analysis. " .
            "Format your response with clear sections and scores where appropriate.";
    }

    protected function getSummarizePrompt(): string
    {
        return "You are an email summarizer. Extract and present key points from email threads concisely. " .
            "Identify main topics, decisions made, action items, and important dates. " .
            "Structure the summary in a clear, scannable format.";
    }

    protected function getTemplatePrompt(): string
    {
        return "You are an email template generator. Create reusable email templates with placeholders " .
            "[like this] for customizable parts. Ensure templates are professional, complete, and " .
            "easy to customize. Include all necessary sections for the template type.";
    }

    public function formatDraftPrompt(string $description, ?string $context = null): string
    {
        $prompt = "Write an email based on this description: {$description}";

        if ($context) {
            $prompt .= "\n\nAdditional context: {$context}";
        }

        return $prompt;
    }

    public function formatResponsePrompt(string $originalEmail, ?string $instructions = null): string
    {
        $prompt = "Original email:\n{$originalEmail}\n\n";

        if ($instructions) {
            $prompt .= "Response instructions: {$instructions}";
        } else {
            $prompt .= "Generate an appropriate response to this email.";
        }

        return $prompt;
    }

    public function formatAnalyzePrompt(string $emailContent): string
    {
        return "Analyze this email and provide feedback:\n\n{$emailContent}\n\n" .
            "Include:\n" .
            "1. Tone analysis\n" .
            "2. Clarity score (1-10)\n" .
            "3. Professionalism score (1-10)\n" .
            "4. Specific suggestions for improvement\n" .
            "5. What works well";
    }

    public function formatSummaryPrompt(string $emailThread): string
    {
        return "Summarize this email thread, highlighting key points, decisions, and action items:\n\n" .
            "{$emailThread}\n\n" .
            "Format the summary with:\n" .
            "- Main topics discussed\n" .
            "- Key decisions made\n" .
            "- Action items and owners\n" .
            "- Important dates/deadlines\n" .
            "- Any unresolved issues";
    }

    public function formatTemplatePrompt(TemplateType $type, ?string $context = null): string
    {
        $description = $type->getDescription();

        $prompt = "Create a {$description} template.";

        if ($context) {
            $prompt .= "\nContext: {$context}";
        }

        $prompt .= "\nInclude placeholders in [brackets] for parts that should be customized.";
        $prompt .= "\nEnsure the template is complete and professional.";

        return $prompt;
    }
}