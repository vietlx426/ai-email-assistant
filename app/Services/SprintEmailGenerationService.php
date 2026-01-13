<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\DeepSeekProvider;

class SprintEmailGenerationService
{
    protected $deepseekProvider;
    protected $emailLearningService;

    public function __construct(DeepSeekProvider $deepseekProvider, EmailLearningService $emailLearningService)
    {
        $this->deepseekProvider = $deepseekProvider;
        $this->emailLearningService = $emailLearningService;
    }

    /**
     * Generate a new sprint email based on user request
     */
    public function generateEmail(string $request, array $context = []): array
    {
        try {
            // Step 1: Find the best matching template using vector search
            $bestTemplate = $this->findBestTemplate($request);

            if (!$bestTemplate) {
                return $this->generateFromScratch($request, $context);
            }

            // Step 2: Extract variables that need to be filled
            $variables = $this->extractVariablesFromTemplate($bestTemplate['template_content']);

            // Step 3: Generate content for variables using AI
            $filledVariables = $this->generateVariableContent($variables, $request, $context, $bestTemplate);

            // Step 4: Replace variables in template
            $generatedEmail = $this->fillTemplate($bestTemplate['template_content'], $filledVariables);

            // Step 5: Final polish with AI
            $polishedEmail = $this->polishEmail($generatedEmail, $request, $bestTemplate['learned_patterns']);

            // Step 6: Save to history
            $this->saveToHistory($request, $polishedEmail, $bestTemplate, $context);

            return [
                'success' => true,
                'email' => [
                    'subject' => $this->generateSubject($request, $context),
                    'content' => $polishedEmail,
                    'template_used' => $bestTemplate['email_type'],
                    'confidence' => $bestTemplate['similarity_score']
                ],
                'template_info' => [
                    'matched_template' => $bestTemplate['email_type'],
                    'similarity_score' => $bestTemplate['similarity_score'],
                    'variables_filled' => array_keys($filledVariables)
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Sprint email generation failed', [
                'request' => $request,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Find best matching template using vector similarity search
     */
    protected function findBestTemplate(string $request): ?array
    {
        // Generate embedding for the user request
        $requestEmbedding = $this->emailLearningService->generateEmbedding($request);

        if (!$requestEmbedding) {
            return null;
        }

        // Get vectors and find corresponding templates
        $vectors = DB::table('email_pattern_vectors')
            ->select([
                'id',
                'pattern_type',
                'embedding',
                'confidence_score',
                'source_email_id'
            ])
            ->get();

        $bestMatch = null;
        $highestSimilarity = 0;

        foreach ($vectors as $vector) {
            if (!$vector->embedding) continue;

            $vectorEmbedding = json_decode($vector->embedding, true);
            if (!$vectorEmbedding) continue;

            $similarity = $this->calculateCosineSimilarity($requestEmbedding, $vectorEmbedding);

            if ($similarity > $highestSimilarity) {
                $highestSimilarity = $similarity;

                // Get the corresponding template for this pattern type
                $template = DB::table('email_templates')
                    ->where('email_type', $vector->pattern_type)
                    ->first();

                if ($template) {
                    $bestMatch = [
                        'id' => $template->id,
                        'email_type' => $template->email_type,
                        'template_content' => $template->template_content,
                        'learned_patterns' => json_decode($template->learned_patterns, true),
                        'similarity_score' => $similarity,
                        'vector_confidence' => $vector->confidence_score
                    ];
                }
            }
        }

        // Only return if similarity is above threshold
        return $highestSimilarity > 0.7 ? $bestMatch : null;
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    protected function calculateCosineSimilarity(array $vector1, array $vector2): float
    {
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        for ($i = 0; $i < count($vector1); $i++) {
            $dotProduct += $vector1[$i] * $vector2[$i];
            $magnitude1 += pow($vector1[$i], 2);
            $magnitude2 += pow($vector2[$i], 2);
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Extract variables from template (things like {{sprint_name}})
     */
    protected function extractVariablesFromTemplate(string $template): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $template, $matches);
        return array_unique($matches[1]);
    }

    /**
     * Generate content for template variables using AI
     */
    protected function generateVariableContent(array $variables, string $request, array $context, array $template): array
    {
        $filledVariables = [];

        foreach ($variables as $variable) {
            // Check if user provided this variable in context
            if (isset($context[$variable])) {
                $filledVariables[$variable] = $context[$variable];
                continue;
            }

            // Generate content for this variable using AI
            $variableContent = $this->generateSingleVariable($variable, $request, $context, $template);
            $filledVariables[$variable] = $variableContent;
        }

        return $filledVariables;
    }

    /**
     * Generate content for a single variable
     */
    protected function generateSingleVariable(string $variable, string $request, array $context, array $template): string
    {
        $prompt = $this->buildVariablePrompt($variable, $request, $context, $template);

        $response = $this->deepseekProvider->generateText($prompt, [
            'max_tokens' => 150,
            'temperature' => 0.7
        ]);

        return $response['response'] ?? "[{$variable}]";
    }

    /**
     * Build prompt for generating variable content
     */
    protected function buildVariablePrompt(string $variable, string $request, array $context, array $template): string
    {
        $learnedPatterns = $template['learned_patterns'];

        $prompt = "Generate content for the variable '{$variable}' in a sprint email.\n\n";
        $prompt .= "Context: {$request}\n\n";

        if (!empty($context)) {
            $prompt .= "Additional context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n\n";
        }

        $prompt .= "Writing style to match:\n";
        if (isset($learnedPatterns['tone'])) {
            $prompt .= "- Tone: {$learnedPatterns['tone']}\n";
        }
        if (isset($learnedPatterns['technical_terms'])) {
            $prompt .= "- Technical terms used: " . implode(', ', $learnedPatterns['technical_terms']) . "\n";
        }

        $prompt .= "\nGenerate appropriate content for '{{" . $variable . "}}'. ";
        $prompt .= "Keep it concise and match the professional but friendly tone. ";
        $prompt .= "Respond with ONLY the content, no explanations.";

        return $prompt;
    }

    /**
     * Fill template with generated variables
     */
    protected function fillTemplate(string $template, array $variables): string
    {
        $filled = $template;

        foreach ($variables as $variable => $content) {
            $filled = str_replace("{{" . $variable . "}}", $content, $filled);
        }

        return $filled;
    }

    /**
     * Polish the email using AI
     */
    protected function polishEmail(string $email, string $request, array $learnedPatterns): string
    {
        $prompt = "Polish this sprint email to make it more natural and professional.\n\n";
        $prompt .= "Original request: {$request}\n\n";
        $prompt .= "Email to polish:\n{$email}\n\n";
        $prompt .= "Maintain the structure and content, just improve flow and naturalness.\n";
        $prompt .= "Keep the tone: " . ($learnedPatterns['tone'] ?? 'professional but friendly') . "\n\n";
        $prompt .= "Return only the polished email content:";

        $response = $this->deepseekProvider->generateText($prompt, [
            'max_tokens' => 800,
            'temperature' => 0.3
        ]);

        return $response['response'] ?? $email;
    }

    /**
     * Generate email subject
     */
    protected function generateSubject(string $request, array $context): string
    {
        $prompt = "Generate a professional email subject line for this sprint email request:\n";
        $prompt .= "{$request}\n\n";

        if (isset($context['sprint_name'])) {
            $prompt .= "Sprint: {$context['sprint_name']}\n";
        }

        $prompt .= "Make it clear, professional, and under 60 characters.\n";
        $prompt .= "Examples: 'Sprint 3 Commitment', 'Weekly Sprint Update', 'Sprint Retrospective Summary'\n\n";
        $prompt .= "Subject line:";

        $response = $this->deepseekProvider->generateText($prompt, [
            'max_tokens' => 50,
            'temperature' => 0.5
        ]);

        return trim($response['response'] ?? 'Sprint Email');
    }

    /**
     * Generate email from scratch if no template matches
     */
    protected function generateFromScratch(string $request, array $context): array
    {
        // Get learned patterns from any sprint email to maintain style
        $learnedPatterns = DB::table('email_templates')
            ->whereIn('email_type', ['sprint_commitment', 'sprint_update'])
            ->first();

        $patterns = $learnedPatterns ? json_decode($learnedPatterns->learned_patterns, true) : [];

        $prompt = $this->buildFromScratchPrompt($request, $context, $patterns);

        $response = $this->deepseekProvider->generateText($prompt, [
            'max_tokens' => 1000,
            'temperature' => 0.7
        ]);

        $generatedEmail = $response ?? "Unable to generate email.";
        return [
            'success' => true,
            'email' => [
                'subject' => $this->generateSubject($request, $context),
                'content' => $generatedEmail,
                'template_used' => 'generated_from_scratch',
                'confidence' => 0.5
            ],
            'template_info' => [
                'matched_template' => 'none',
                'similarity_score' => 0,
                'generated_from_scratch' => true
            ]
        ];
    }

    /**
     * Build prompt for generating email from scratch
     */
    protected function buildFromScratchPrompt(string $request, array $context, array $patterns): string
    {
        $prompt = "Generate a professional sprint email based on this request:\n";
        $prompt .= "{$request}\n\n";

        if (!empty($context)) {
            $prompt .= "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n\n";
        }

        if (!empty($patterns)) {
            $prompt .= "Writing style to match:\n";
            if (isset($patterns['tone'])) {
                $prompt .= "- Tone: {$patterns['tone']}\n";
            }
            if (isset($patterns['structure_elements'])) {
                $prompt .= "- Structure: " . implode(', ', $patterns['structure_elements']) . "\n";
            }
        }

        $prompt .= "\nGenerate a complete, professional sprint email. ";
        $prompt .= "Use markdown formatting where appropriate. ";
        $prompt .= "Be specific and actionable.";

        return $prompt;
    }

    /**
     * Save generated email to history
     */
    protected function saveToHistory(string $request, string $generatedEmail, array $template, array $context): void
    {
        DB::table('sprint_email_history')->insert([
            'user_request' => $request,
            'generated_subject' => $this->generateSubject($request, $context),
            'generated_content' => $generatedEmail,
            'template_used' => $template['email_type'] ?? 'from_scratch',
            'context_data' => json_encode($context),
            'ai_confidence' => $template['similarity_score'] ?? 0.5,
            'user_feedback' => null, // Will be updated when user provides feedback
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}