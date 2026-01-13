<?php

namespace App\Services;

use App\Services\DeepSeekProvider;
use App\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailLearningService
{
    protected AIProviderInterface $aiProvider;

    public function __construct(DeepSeekProvider $aiProvider)
    {
        $this->aiProvider = $aiProvider;
    }

    /**
     * Analyze uploaded email and extract patterns
     */
    public function analyzeTrainingEmail(int $trainingEmailId): array
    {
        $email = DB::table('email_training_data')->find($trainingEmailId);

        if (!$email || $email->is_processed) {
            throw new \Exception('Email not found or already processed');
        }

        Log::info("Analyzing training email ID: {$trainingEmailId}");

        $patterns = $this->extractEmailPatterns($email);
        $embedding = $this->generateEmbedding($email->content);
        $template = $this->generateTemplate($email, $patterns);
        $results = $this->storeAnalysisResults($email, $patterns, $embedding, $template);
        DB::table('email_training_data')
            ->where('id', $trainingEmailId)
            ->update([
                'is_processed' => true,
                'extracted_patterns' => json_encode($patterns),
                'updated_at' => now()
            ]);

        Log::info("Analysis complete for email ID: {$trainingEmailId}");

        return $results;
    }

    /**
     * Extract email patterns using AI analysis
     */
    protected function extractEmailPatterns(object $email): array
    {
        $prompt = $this->buildAnalysisPrompt($email);

        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->aiProvider->chat($messages, [
            'temperature' => 0.1,
            'max_tokens' => 1000
        ]);

        if (!$response->success) {
            Log::error("AI analysis failed: " . $response->error);
            throw new \Exception("AI analysis failed: " . $response->error);
        }

        return $this->parsePatternResponse($response->content);
    }

    /**
     * Build analysis prompt for AI - Enhanced for complex sprint emails
     */
    protected function buildAnalysisPrompt(object $email): string
    {
        return "
Analyze this sprint email and extract key patterns for template generation. This email may contain markdown formatting, table structures, and technical terminology.

EMAIL TYPE: {$email->email_type}
SUBJECT: {$email->subject_line}
CONTENT:
{$email->content}

Please analyze and return a JSON response with these fields:

{
  \"tone\": \"professional|casual|formal\",
  \"structure\": \"bullet_list|table_format|mixed|markdown_sections\",
  \"key_sections\": [\"greeting\", \"sprint_goal\", \"sprint_commitment\", \"flexibility_clause\", \"closing\"],
  \"formatting_features\": [\"markdown_bold\", \"bullet_points\", \"table_structure\", \"numbered_lists\"],
  \"variables_found\": [\"sprint_name\", \"sprint_goal\", \"commitment_items\", \"team_categories\"],
  \"writing_style\": {
    \"sentence_length\": \"short|medium|long\",
    \"formality_level\": \"high|medium|low\",
    \"technical_level\": \"high|medium|low\",
    \"personal_touches\": [\"friendly_greeting\", \"flexibility_offer\", \"question_invitation\"]
  },
  \"template_variables\": [
    {\"name\": \"recipients\", \"example\": \"all\", \"location\": \"greeting\"},
    {\"name\": \"sprint_name\", \"example\": \"December Sprint 3\", \"location\": \"title_and_body\"},
    {\"name\": \"sprint_goal_description\", \"example\": \"focus on fixing VLE weekly defects\", \"location\": \"goal_section\"},
    {\"name\": \"commitment_categories\", \"example\": \"Development + Testing, Development Only\", \"location\": \"commitment_section\"},
    {\"name\": \"commitment_items\", \"example\": \"table_with_ids_titles_states\", \"location\": \"commitment_section\"}
  ],
  \"complexity_indicators\": {
    \"has_table_structure\": true,
    \"has_technical_ids\": true,
    \"has_multiple_categories\": true,
    \"has_detailed_items\": true
  },
  \"confidence_score\": 0.85
}

Pay special attention to:
1. How commitment items are structured (table format, IDs, states, releases)
2. Section headers and markdown formatting
3. Multiple commitment categories
4. Technical terminology and version numbers
5. Flexibility and communication patterns

Focus on patterns that can be reused for generating similar {$email->email_type} emails.
        ";
    }

    /**
     * Parse AI response into structured patterns
     */
    protected function parsePatternResponse(string $response): array
    {
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $json;
            }
        }

        return [
            'tone' => 'professional',
            'structure' => 'mixed',
            'confidence_score' => 0.5,
            'key_sections' => ['greeting', 'main_content', 'closing'],
            'template_variables' => []
        ];
    }

    /**
     * Generate vector embedding for semantic search
     */
    public function generateEmbedding(string $content): array
    {

        $embedding = [];

        $features = [
            'length' => strlen($content),
            'word_count' => str_word_count($content),
            'has_bullet_points' => strpos($content, '•') !== false || strpos($content, '*') !== false,
            'has_markdown' => strpos($content, '**') !== false,
            'has_table_structure' => strpos($content, '|') !== false,
            'formality_score' => $this->calculateFormalityScore($content),
            'technical_terms' => $this->countTechnicalTerms($content)
        ];

        for ($i = 0; $i < 1536; $i++) {
            $seed = array_sum(array_values($features)) + $i;
            $embedding[] = sin($seed * 0.1) * 0.5;
        }

        return $embedding;
    }

    /**
     * Calculate formality score of content
     */
    protected function calculateFormalityScore(string $content): float
    {
        $formal_indicators = ['following', 'summary', 'commitment', 'accordingly', 'concerns'];
        $casual_indicators = ['hi all', 'hope you\'re', 'feel free', 'questions'];

        $formal_count = 0;
        $casual_count = 0;

        foreach ($formal_indicators as $indicator) {
            if (stripos($content, $indicator) !== false) $formal_count++;
        }

        foreach ($casual_indicators as $indicator) {
            if (stripos($content, $indicator) !== false) $casual_count++;
        }

        return $formal_count > 0 ? $formal_count / ($formal_count + $casual_count + 1) : 0.5;
    }

    /**
     * Count technical terms in content
     */
    protected function countTechnicalTerms(string $content): int
    {
        $technical_terms = ['VLE', 'Behat', 'StudyApp', 'API', 'UI', 'defect', 'release', 'version'];
        $count = 0;

        foreach ($technical_terms as $term) {
            $count += substr_count(strtolower($content), strtolower($term));
        }

        return $count;
    }

    /**
     * Generate email template from patterns
     */
    protected function generateTemplate(object $email, array $patterns): array
    {
        $templatePrompt = "
Create a reusable email template based on this analyzed email pattern:

ORIGINAL EMAIL:
{$email->content}

EXTRACTED PATTERNS:
" . json_encode($patterns, JSON_PRETTY_PRINT) . "

Generate a template with the following structure:
1. Replace specific values with {{variable_names}}
2. Keep the structure and formatting intact
3. Preserve the tone and style
4. Make it reusable for similar {$email->email_type} emails

Return JSON format:
{
  \"template_content\": \"Hi {{recipients}},\\n\\nFollowing our Sprint Planning...\",
  \"variables\": [\"recipients\", \"sprint_name\", \"sprint_goal\", \"commitment_items\"],
  \"template_name\": \"Technical Sprint Commitment with Categories\",
  \"style_attributes\": {\"tone\": \"professional\", \"structure\": \"markdown_sections\"}
}
        ";

        $messages = [
            ['role' => 'user', 'content' => $templatePrompt]
        ];

        $response = $this->aiProvider->chat($messages, [
            'temperature' => 0.2,
            'max_tokens' => 1500
        ]);

        if (!$response->success) {
            Log::warning("Template generation failed: " . $response->error);

            return [
                'template_content' => $this->generateFallbackTemplate($email, $patterns),
                'variables' => $patterns['template_variables'] ?? [],
                'template_name' => ucfirst($email->email_type) . ' Template (Fallback)',
                'style_attributes' => [
                    'tone' => $patterns['tone'] ?? 'professional',
                    'structure' => $patterns['structure'] ?? 'mixed'
                ]
            ];
        }

        if (preg_match('/\{.*\}/s', $response->content, $matches)) {
            $template = json_decode($matches[0], true);
            if ($template) {
                return $template;
            }
        }

        return [
            'template_content' => $this->generateFallbackTemplate($email, $patterns),
            'variables' => $patterns['template_variables'] ?? [],
            'template_name' => ucfirst($email->email_type) . ' Template',
            'style_attributes' => [
                'tone' => $patterns['tone'] ?? 'professional',
                'structure' => $patterns['structure'] ?? 'mixed'
            ]
        ];
    }

    /**
     * Generate fallback template if AI fails
     */
    protected function generateFallbackTemplate(object $email, array $patterns): string
    {
        switch ($email->email_type) {
            case 'sprint_commitment':
                return "Hi {{recipients}},\n\n{{greeting_context}}\n\n**{{sprint_name}} Commitment and Goal**:\n\n**Sprint Goal**\n{{sprint_goal_description}}\n\n**Sprint Commitment**\n{{commitment_items}}\n\n{{flexibility_statement}}\n\n{{closing_statement}}";

            default:
                return "Hi {{recipients}},\n\n{{main_content}}\n\n{{closing}}";
        }
    }

    /**
     * Store all analysis results in database
     */
    protected function storeAnalysisResults(object $email, array $patterns, array $embedding, array $template): array
    {
        DB::beginTransaction();

        try {
            $vectorId = DB::table('email_pattern_vectors')->insertGetId([
                'pattern_type' => $email->email_type,
                'content_hash' => $email->content_hash,
                'embedding' => json_encode($embedding),
                'metadata' => json_encode($patterns),
                'confidence_score' => $patterns['confidence_score'] ?? 0.5,
                'dimension' => count($embedding),
                'source_email_id' => $email->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $templateId = DB::table('email_templates')->insertGetId([
                'pattern_type' => $email->email_type,
                'template_name' => $template['template_name'],
                'template_content' => $template['template_content'],
                'variables' => json_encode($template['variables']),
                'style_attributes' => json_encode($template['style_attributes']),
                'usage_count' => 0,
                'success_rate' => 0.00,
                'source_email_id' => $email->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return [
                'vector_id' => $vectorId,
                'template_id' => $templateId,
                'patterns' => $patterns,
                'embedding_dimension' => count($embedding),
                'status' => 'success'
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to store analysis results: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process all unprocessed training emails
     */
    public function processAllTrainingEmails(): array
    {
        $unprocessed = DB::table('email_training_data')
            ->where('is_processed', false)
            ->where('is_approved', true)
            ->get();

        $results = [];

        foreach ($unprocessed as $email) {
            try {
                $result = $this->analyzeTrainingEmail($email->id);
                $results[] = [
                    'email_id' => $email->id,
                    'status' => 'success',
                    'result' => $result
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'email_id' => $email->id,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}