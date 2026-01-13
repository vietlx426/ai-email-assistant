<?php

namespace App\Console\Commands;

use App\Contracts\AIProviderInterface;
use Illuminate\Console\Command;

class TestDeepSeek extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deepseek:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test DeepSeek API connection';

    /**
     * Execute the console command.
     */
    public function handle(AIProviderInterface $aiProvider)
    {
        $this->info('Testing DeepSeek API Connection...');

        $apiKey = config('ai.providers.deepseek.api_key');

        if (!$apiKey || $apiKey === 'your_deepseek_api_key_here') {
            $this->error('No API key found! Please add your DeepSeek API key to .env file:');
            $this->line('   DEEPSEEK_API_KEY=sk-your-actual-key-here');
            return 1;
        }

        $this->line('   API Key: ' . substr($apiKey, 0, 10) . '...');
        $this->line('   Model: ' . config('ai.providers.deepseek.model'));
        $this->line('');

        try {
            $this->info('Testing basic chat functionality...');

            $response = $aiProvider->chat([
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => 'Respond with exactly: "DeepSeek is connected and working!"']
            ]);

            if ($response->success) {
                $this->info('Basic chat: SUCCESS');
                $this->line('   Response: ' . $response->content);
            } else {
                $this->error('Basic chat: FAILED');
                $this->error('   Error: ' . $response->error);
                return 1;
            }

            $this->info('');
            $this->info('Testing email drafting...');

            $response = $aiProvider->chat([
                ['role' => 'system', 'content' => 'You are a professional email writer. Write a brief email.'],
                ['role' => 'user', 'content' => 'Write a short email thanking someone for a meeting. Keep it under 50 words.']
            ]);

            if ($response->success) {
                $this->info('Email drafting: SUCCESS');
                $this->line('   Generated email:');
                $this->line('   ' . str_replace("\n", "\n   ", $response->content));

                if (isset($response->usage['total_tokens'])) {
                    $this->line('');
                    $this->line('   Tokens used: ' . $response->usage['total_tokens']);
                }
            } else {
                $this->error('Email drafting: FAILED');
                $this->error('   Error: ' . $response->error);
                return 1;
            }

            $this->info('');
            $this->info('All tests passed! DeepSeek is ready to use.');

            $this->info('');
            $this->info('Example API endpoint test:');
            $this->line('   curl -X POST http://localhost:8000/api/v1/email/draft \\');
            $this->line('     -H "Content-Type: application/json" \\');
            $this->line('     -d \'{"description": "Thank the team for their hard work on the project", "tone": "friendly"}\'');

            return 0;

        } catch (\Exception $e) {
            $this->error('Unexpected error: ' . $e->getMessage());
            $this->line('');
            $this->line('Troubleshooting tips:');
            $this->line('1. Check your API key is correct');
            $this->line('2. Ensure you have internet connection');
            $this->line('3. Verify DeepSeek API is not down');
            $this->line('4. Check your firewall settings');
            return 1;
        }
    }
}