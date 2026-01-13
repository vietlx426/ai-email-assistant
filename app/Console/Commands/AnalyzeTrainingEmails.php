<?php

namespace App\Console\Commands;

use App\Services\EmailLearningService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyzeTrainingEmails extends Command
{
    protected $signature = 'sprint:analyze-emails {--id= : Analyze specific email ID} {--all : Analyze all unprocessed emails}';
    protected $description = 'Analyze training emails and extract patterns for Sprint Email Learning System';

    protected EmailLearningService $learningService;

    public function __construct(EmailLearningService $learningService)
    {
        parent::__construct();
        $this->learningService = $learningService;
    }

    public function handle()
    {
        $this->info('🧠 Sprint Email Learning System - Email Analysis');
        $this->line('================================================');

        // Check if we have training data
        $totalEmails = DB::table('email_training_data')->count();
        $unprocessed = DB::table('email_training_data')->where('is_processed', false)->count();

        $this->info(" Training emails status:");
        $this->line("  Total emails: {$totalEmails}");
        $this->line("  Unprocessed: {$unprocessed}");
        $this->line("  Already processed: " . ($totalEmails - $unprocessed));
        $this->line('');

        if ($totalEmails === 0) {
            $this->error('X No training emails found. Run the seeder first:');
            $this->line('   php artisan db:seed --class=SprintEmailSeeder');
            return 1;
        }

        // Handle specific email ID
        if ($id = $this->option('id')) {
            return $this->analyzeSpecificEmail($id);
        }

        // Handle analyze all
        if ($this->option('all')) {
            return $this->analyzeAllEmails();
        }

        // Interactive mode - show available emails
        $this->showAvailableEmails();

        if ($unprocessed === 0) {
            $this->info('✓ All emails have been processed!');
            return 0;
        }

        if ($this->confirm('Would you like to analyze all unprocessed emails?')) {
            return $this->analyzeAllEmails();
        }

        $this->info('Use --all flag to analyze all emails, or --id=X for specific email');
        return 0;
    }

    protected function analyzeSpecificEmail(int $id): int
    {
        $this->info(" Analyzing email ID: {$id}");

        try {
            $result = $this->learningService->analyzeTrainingEmail($id);

            $this->info('✓ Analysis complete!');
            $this->line(" Results:");
            $this->line("  Vector ID: {$result['vector_id']}");
            $this->line("  Template ID: {$result['template_id']}");
            $this->line("  Embedding dimensions: {$result['embedding_dimension']}");
            $this->line("  Confidence score: " . ($result['patterns']['confidence_score'] ?? 'N/A'));

            return 0;

        } catch (\Exception $e) {
            $this->error("X Analysis failed: " . $e->getMessage());
            return 1;
        }
    }

    protected function analyzeAllEmails(): int
    {
        $this->info(' Analyzing all unprocessed emails...');
        $this->line('');

        try {
            $results = $this->learningService->processAllTrainingEmails();

            $successful = collect($results)->where('status', 'success')->count();
            $failed = collect($results)->where('status', 'failed')->count();

            $this->info("✓ Batch analysis complete!");
            $this->line(" Results:");
            $this->line("  Successful: {$successful}");
            $this->line("  Failed: {$failed}");

            if ($failed > 0) {
                $this->warn('⚠️  Some emails failed to process:');
                foreach ($results as $result) {
                    if ($result['status'] === 'failed') {
                        $this->line("  Email ID {$result['email_id']}: {$result['error']}");
                    }
                }
            }

            // Show summary statistics
            $this->showAnalysisStatistics();

            return $failed > 0 ? 1 : 0;

        } catch (\Exception $e) {
            $this->error("X Batch analysis failed: " . $e->getMessage());
            return 1;
        }
    }

    protected function showAvailableEmails(): void
    {
        $emails = DB::table('email_training_data')
            ->select('id', 'email_type', 'subject_line', 'is_processed')
            ->orderBy('id')
            ->get();

        $this->info(' Available training emails:');
        $this->line('');

        $headers = ['ID', 'Type', 'Subject', 'Processed'];
        $rows = [];

        foreach ($emails as $email) {
            $rows[] = [
                $email->id,
                $email->email_type,
                substr($email->subject_line ?? 'No subject', 0, 50),
                $email->is_processed ? '✓' : ''
            ];
        }

        $this->table($headers, $rows);
        $this->line('');
    }

    protected function showAnalysisStatistics(): void
    {
        $vectorCount = DB::table('email_pattern_vectors')->count();
        $templateCount = DB::table('email_templates')->count();

        $patternTypes = DB::table('email_pattern_vectors')
            ->selectRaw('pattern_type, COUNT(*) as count')
            ->groupBy('pattern_type')
            ->get();

        $this->line('');
        $this->info(' Learning System Statistics:');
        $this->line("  Vector embeddings: {$vectorCount}");
        $this->line("  Email templates: {$templateCount}");
        $this->line('');

        if ($patternTypes->count() > 0) {
            $this->info('📋 Patterns by type:');
            foreach ($patternTypes as $type) {
                $this->line("  {$type->pattern_type}: {$type->count}");
            }
        }

        $this->line('');
        $this->info(' Next steps:');
        $this->line('  1. Test email generation: php artisan sprint:generate-email');
        $this->line('  2. View learned templates: php artisan sprint:list-templates');
        $this->line('  3. Access web interface at /sprint-email');
    }
}