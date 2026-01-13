<?php

namespace App\Jobs;

use App\Actions\AnalyzeEmailAction;
use App\DTOs\EmailAnalysisRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBulkEmailAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $emails,
        private string $callbackUrl
    ) {}

    public function handle(AnalyzeEmailAction $action): void
    {
        $results = [];

        foreach ($this->emails as $email) {
            try {
                $dto = new EmailAnalysisRequest($email['content']);
                $result = $action->execute($dto);

                $results[] = [
                    'id' => $email['id'] ?? null,
                    'success' => $result->success,
                    'analysis' => $result->content,
                    'error' => $result->error,
                ];
            } catch (\Exception $e) {
                Log::error('Bulk analysis error', [
                    'email_id' => $email['id'] ?? null,
                    'error' => $e->getMessage(),
                ]);

                $results[] = [
                    'id' => $email['id'] ?? null,
                    'success' => false,
                    'error' => 'Analysis failed: ' . $e->getMessage(),
                ];
            }
        }

        $this->notifyCallback($results);
    }

    protected function notifyCallback(array $results): void
    {
    }
}