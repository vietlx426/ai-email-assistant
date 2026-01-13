<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SprintEmailGenerationService;
use Illuminate\Support\Facades\DB;

class GenerateSprintEmail extends Command
{
    protected $signature = 'sprint:generate-email 
                            {--request= : The email request/prompt}
                            {--interactive : Run in interactive mode}';

    protected $description = 'Generate a sprint email using learned patterns';

    protected SprintEmailGenerationService $generationService;

    public function __construct(SprintEmailGenerationService $generationService)
    {
        parent::__construct();
        $this->generationService = $generationService;
    }

    public function handle()
    {
        $this->info('Sprint Email Generator');
        $this->line('Using your learned patterns and AI to generate personalized sprint emails');

        $templateCount = DB::table('email_templates')->count();
        if ($templateCount > 0) {
            $this->info("Found {$templateCount} learned templates");
        } else {
            $this->warn('No templates found. Run sprint:analyze-emails first to learn from your emails.');
            return 1;
        }

        $request = $this->getRequest();
        if (!$request) {
            $this->error('No request provided');
            return 1;
        }

        $context = [];
        if ($this->option('interactive')) {
            $context = $this->getInteractiveContext();
        }

        $this->info('Generating email using AI...');

        try {
            $result = $this->generationService->generateEmail($request, $context);

            if (is_array($result)) {
                if (!empty($result['success']) && !empty($result['email'])) {
                    $email = $result['email'];
                    $templateInfo = $result['template_info'] ?? [];

                    $this->displayEmail($email, $templateInfo);

                    if (!empty($email['content']) && $email['content'] !== 'Unable to generate email.') {
                        $this->saveToHistory($request, $email, $context);
                    } else {
                        $this->error('Failed to generate email content');
                        $this->line('This might be due to:');
                        $this->line('1. Mock mode is enabled (check AI_MOCK_MODE in .env)');
                        $this->line('2. AI provider is not configured properly');
                        $this->line('3. No matching templates found');
                    }
                } else {
                    $this->error('Generation failed: Invalid response structure');
                }
            } else {
                $this->displaySimpleEmail($result);
            }

            if ($this->confirm('Would you like to provide feedback on this email?', false)) {
                $this->collectFeedback();
            }

        } catch (\Exception $e) {
            $this->error('X Error generating email: ' . $e->getMessage());
            $this->line('Debug info:');
            $this->line('File: ' . $e->getFile());
            $this->line('Line: ' . $e->getLine());
            return 1;
        }

        return 0;
    }

    protected function getRequest(): ?string
    {
        if ($this->option('request')) {
            return $this->option('request');
        }

        if ($this->option('interactive')) {
            return $this->ask('What type of sprint email would you like to generate?');
        }

        $this->error('Please provide a request using --request="..." or use --interactive mode');
        return null;
    }

    protected function getInteractiveContext(): array
    {
        $this->info(' Let\'s gather some context for your email...');

        $context = [];

        if ($this->confirm('Would you like to specify a sprint number?', true)) {
            $context['sprint_number'] = $this->ask('Sprint number', date('W'));
        }

        if ($this->confirm('Would you like to specify a team name?', false)) {
            $context['team'] = $this->ask('Team name');
        }

        if ($this->confirm('Would you like to add specific accomplishments?', false)) {
            $context['accomplishments'] = $this->ask('List accomplishments (comma-separated)');
        }

        if ($this->confirm('Would you like to add upcoming tasks?', false)) {
            $context['upcoming'] = $this->ask('List upcoming tasks (comma-separated)');
        }

        return $context;
    }

    protected function displayEmail(array $email, array $templateInfo = []): void
    {
        $subject = $email['subject'] ?? 'Sprint Email';
        $content = $email['content'] ?? 'No content generated';

        $this->line(' Generated Email:');
        $this->line(str_repeat('=', 60));
        $this->info("Subject: {$subject}");
        $this->line($content);
        $this->line(str_repeat('=', 60));

        // Display generation details
        $this->line(' Generation Details:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Template Used', $templateInfo['matched_template'] ?? 'none'],
                ['Similarity Score', ($templateInfo['similarity_score'] ?? 0) * 100 . '%'],
                ['AI Confidence', ($email['confidence'] ?? 0.5) * 100 . '%'],
            ]
        );
    }

    protected function displaySimpleEmail(string $email): void
    {
        $this->line(' Generated Email:');
        $this->line(str_repeat('=', 60));
        $this->line($email);
        $this->line(str_repeat('=', 60));
    }

    protected function saveToHistory(string $request, array $email, array $context): void
    {
        try {
            // Get template ID if a template was used
            $templateId = null;
            if (!empty($email['template_used']) && $email['template_used'] !== 'generated_from_scratch') {
                $template = DB::table('email_templates')
                    ->where('name', $email['template_used'])
                    ->first();
                $templateId = $template?->id;
            }

            DB::table('sprint_email_history')->insert([
                'email_type' => $this->determineEmailType($request),
                'input_prompt' => $request,
                'generated_content' => $email['content'] ?? '',
                'template_used_id' => $templateId,
                'sprint_data' => json_encode($context),
                'generation_metadata' => json_encode([
                    'model' => 'deepseek-chat',
                    'provider' => 'deepseek',
                    'template_info' => $email['template_used'] ?? 'generated_from_scratch',
                    'generated_at' => now()->toIso8601String(),
                ]),
                'confidence_score' => $email['confidence'] ?? 0.5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info('Email saved to history');
        } catch (\Exception $e) {
            $this->warn('Could not save to history: ' . $e->getMessage());
        }
    }

    protected function determineEmailType(string $request): string
    {
        $request = strtolower($request);

        if (str_contains($request, 'commitment')) {
            return 'commitment';
        } elseif (str_contains($request, 'update')) {
            return 'update';
        } elseif (str_contains($request, 'goal')) {
            return 'goals';
        } elseif (str_contains($request, 'retrospective') || str_contains($request, 'retro')) {
            return 'retrospective';
        } elseif (str_contains($request, 'planning')) {
            return 'planning';
        } else {
            return 'general';
        }
    }

    protected function collectFeedback(): void
    {
        $rating = $this->choice(
            'How would you rate this email?',
            ['1 - Poor', '2 - Fair', '3 - Good', '4 - Very Good', '5 - Excellent'],
            '3 - Good'
        );

        $rating = (int) substr($rating, 0, 1);

        $feedback = $this->ask('Any specific feedback? (optional)');

        // Here you would save the feedback to the database
        $this->info("✓ Thank you for your feedback! (Rating: {$rating}/5)");
    }
}