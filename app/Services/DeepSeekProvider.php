<?php

namespace App\Services;

use App\Contracts\AIProviderInterface;
use App\DTOs\AIResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://api.deepseek.com/v1';
    private int $maxTokens = 4096;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.deepseek.api_key');
        $this->model = config('ai.providers.deepseek.model', 'deepseek-chat');
    }

    public function chat(array $messages, array $options = []): AIResponse
    {
        try {
            if (config('ai.mock_mode', false)) {
                return $this->mockResponse($messages);
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => $options['temperature'] ?? 0.7,
                    'max_tokens' => $options['max_tokens'] ?? 1000,
                    'top_p' => $options['top_p'] ?? 1.0,
                    'frequency_penalty' => $options['frequency_penalty'] ?? 0,
                    'presence_penalty' => $options['presence_penalty'] ?? 0,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $content = $data['choices'][0]['message']['content'] ?? '';
                $usage = [
                    'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                    'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                    'total_tokens' => $data['usage']['total_tokens'] ?? 0,
                ];
                $metadata = [
                    'model' => $this->model,
                    'provider' => 'deepseek',
                    'raw_response' => $data
                ];

                return AIResponse::success($content, $usage, $metadata);
            }

            $errorMessage = "DeepSeek API error: " . $response->body();
            Log::error($errorMessage);
            return AIResponse::failure($errorMessage);

        } catch (\Exception $e) {
            Log::error('DeepSeek API exception', [
                'error' => $e->getMessage(),
                'messages' => $messages
            ]);

            return AIResponse::failure("Failed to generate response: " . $e->getMessage());
        }
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $messages = [
            ['role' => 'system', 'content' => $options['system_prompt'] ?? 'You are a helpful AI assistant.'],
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->chat($messages, $options);

        if (!$response->success) {
            throw new \Exception($response->error ?? 'Failed to generate text');
        }

        return $response->content ?? '';
    }

    public function generateResponse(string $prompt, array $options = []): AIResponse
    {
        $messages = [
            ['role' => 'system', 'content' => $options['system_prompt'] ?? 'You are a helpful AI assistant.'],
            ['role' => 'user', 'content' => $prompt]
        ];

        return $this->chat($messages, $options);
    }

    public function generateEmbedding(string $text): array
    {

        if (config('ai.mock_embeddings', false) || config('ai.mock_mode', false)) {
            return $this->generateMockEmbedding($text);
        }

        throw new \Exception('DeepSeek does not provide embedding generation. Consider using OpenAI or a local model.');
    }

    private function generateMockEmbedding(string $text): array
    {
        $hash = md5($text);
        $embedding = [];

        for ($i = 0; $i < 1536; $i++) {
            $seed = hexdec(substr($hash, ($i * 2) % 32, 2));
            $embedding[] = sin($seed + $i) * 0.5 + cos($seed - $i) * 0.5;
        }

        $magnitude = sqrt(array_sum(array_map(fn($x) => $x * $x, $embedding)));
        return array_map(fn($x) => $x / $magnitude, $embedding);
    }

    public function analyzeText(string $text, string $analysisType = 'general'): array
    {
        $prompts = [
            'tone' => "Analyze the tone and writing style of this text. Identify: formality level, emotional tone, professional indicators.",
            'structure' => "Analyze the structure of this text. Identify: opening style, main sections, closing style, formatting patterns.",
            'patterns' => "Extract recurring patterns, phrases, and technical terminology from this text.",
            'general' => "Analyze this text comprehensively including tone, structure, and key patterns."
        ];

        $systemPrompt = 'You are an expert text analyst. Provide detailed JSON-formatted analysis.';
        $userPrompt = ($prompts[$analysisType] ?? $prompts['general']) . "\n\nText:\n" . $text;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        $response = $this->chat($messages, [
            'temperature' => 0.3,
            'max_tokens' => 500
        ]);

        if (!$response->success) {
            return [
                'error' => $response->error,
                'type' => $analysisType,
                'timestamp' => now()->toIso8601String()
            ];
        }

        $content = $response->content ?? '';
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');

        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonStr = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
            $parsed = json_decode($jsonStr, true);
            if ($parsed) {
                return $parsed;
            }
        }

        return [
            'analysis' => $content,
            'type' => $analysisType,
            'timestamp' => now()->toIso8601String()
        ];
    }

    private function mockResponse(array $messages): AIResponse
    {
        $lastMessage = end($messages);
        $prompt = $lastMessage['content'] ?? '';

        $mockResponses = [
            'email' => "Subject: Weekly Sprint Update\n\nDear Team,\n\nI hope this email finds you well. Here's our weekly sprint update:\n\n- Completed user authentication module\n- Fixed critical bugs in payment system\n- Updated documentation\n\nBest regards,\nAI Assistant",
            'analysis' => '{"tone": "professional", "structure": "formal", "patterns": ["greeting", "bullet points", "closing"]}',
            'default' => "This is a mock response for testing. Your actual request was: " . substr($prompt, 0, 100) . "..."
        ];

        $responseType = 'default';
        if (stripos($prompt, 'email') !== false) {
            $responseType = 'email';
        } elseif (stripos($prompt, 'analyze') !== false) {
            $responseType = 'analysis';
        }

        $content = $mockResponses[$responseType];

        return AIResponse::success(
            content: $content,
            usage: [
                'prompt_tokens' => str_word_count($prompt),
                'completion_tokens' => str_word_count($content),
                'total_tokens' => str_word_count($prompt) + str_word_count($content),
            ],
            metadata: [
                'model' => $this->model,
                'provider' => 'deepseek',
                'mock_mode' => true
            ]
        );
    }

    public function isAvailable(): bool
    {
        try {
            if (config('ai.mock_mode', false)) {
                return true;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])
                ->timeout(5)
                ->get("{$this->baseUrl}/models");

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('DeepSeek availability check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getInfo(): array
    {
        return [
            'name' => 'DeepSeek',
            'model' => $this->model,
            'features' => [
                'text_generation' => true,
                'embeddings' => false,
                'streaming' => false,
                'function_calling' => false,
            ],
            'cost_per_1k_tokens' => [
                'input' => 0.00014,
                'output' => 0.00028,
            ],
            'max_tokens' => $this->maxTokens,
            'mock_mode' => config('ai.mock_mode', false),
        ];
    }
}