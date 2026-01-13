<?php

namespace Tests\Feature;

use App\Contracts\AIProviderInterface;
use App\DTOs\AIResponse;
use App\Models\EmailHistory;
use App\Models\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock AI provider for testing
        $this->mockAIProvider();
    }

    protected function mockAIProvider(): void
    {
        $this->mock(AIProviderInterface::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->andReturn(AIResponse::success(
                    'This is a test email response',
                    ['tokens' => 100, 'cost' => 0.01]
                ));
        });
    }

    public function test_can_draft_email()
    {
        $response = $this->postJson('/api/v1/email/draft', [
            'description' => 'Write an email to schedule a meeting',
            'tone' => 'professional',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'content',
                    'usage',
                ],
            ]);

        $this->assertDatabaseHas('email_history', [
            'operation' => 'draft',
            'tone' => 'professional',
        ]);
    }

    public function test_validates_draft_email_request()
    {
        $response = $this->postJson('/api/v1/email/draft', [
            'description' => 'short', // Too short
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    public function test_can_generate_response()
    {
        $response = $this->postJson('/api/v1/email/response', [
            'original_email' => 'Hello, can we schedule a meeting next week?',
            'tone' => 'friendly',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_can_analyze_email()
    {
        $response = $this->postJson('/api/v1/email/analyze', [
            'email_content' => 'Dear Sir, I hope this email finds you well...',
        ]);

        $response->assertStatus(200);
    }

    public function test_can_summarize_thread()
    {
        $thread = "From: John\nHello, can we meet?\n\nFrom: Jane\nSure, when works for you?";

        $response = $this->postJson('/api/v1/email/summarize', [
            'email_thread' => $thread,
        ]);

        $response->assertStatus(200);
    }

    public function test_can_generate_template()
    {
        $response = $this->postJson('/api/v1/email/template', [
            'template_type' => 'meeting_request',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('email_templates', [
            'type' => 'meeting_request',
        ]);
    }

    public function test_can_get_history()
    {
        // Create some history
        EmailHistory::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/email/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'operation',
                        'created_at',
                    ],
                ],
            ]);
    }

    public function test_can_rate_generation()
    {
        $history = EmailHistory::factory()->create();

        $response = $this->postJson("/api/v1/email/history/{$history->id}/rate", [
            'rating' => 5,
            'feedback' => 'Great response!',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('email_history', [
            'id' => $history->id,
            'rating' => 5,
            'feedback' => 'Great response!',
        ]);
    }

    public function test_caches_analysis_results()
    {
        $emailContent = 'Test email content for caching';

        // First request
        $response1 = $this->postJson('/api/v1/email/analyze', [
            'email_content' => $emailContent,
        ]);

        // Second request (should be cached)
        $response2 = $this->postJson('/api/v1/email/analyze', [
            'email_content' => $emailContent,
        ]);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Check that only one database entry was created
        $this->assertEquals(1, EmailHistory::where('operation', 'analyze')->count());
    }
}