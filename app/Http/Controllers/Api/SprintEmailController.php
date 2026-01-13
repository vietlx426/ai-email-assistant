<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\SprintEmailGenerationService;
use App\Services\EmailLearningService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SprintEmailController extends Controller
{
    protected $generationService;
    protected $learningService;

    public function __construct(
        SprintEmailGenerationService $generationService,
        EmailLearningService $learningService
    ) {
        $this->generationService = $generationService;
        $this->learningService = $learningService;
    }

    /**
     * Generate a new sprint email
     */
    public function generateEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'request' => 'required|string|min:10|max:500',
            'context' => 'sometimes|array',
            'context.sprint_name' => 'sometimes|string|max:100',
            'context.email_type' => 'sometimes|in:commitment,update,retrospective',
            'context.additional_context' => 'sometimes|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid input',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $userRequest = $request->input('request');
            $context = $request->input('context', []);

            $result = $this->generationService->generateEmail($userRequest, $context);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload and analyze training emails
     */
    public function uploadTrainingEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:200',
            'content' => 'required|string|min:50',
            'email_type' => 'required|in:sprint_commitment,sprint_update,retrospective,planning,other',
            'sender_name' => 'sometimes|string|max:100',
            'recipient' => 'sometimes|string|max:200'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid input',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Save to training data
            $trainingId = DB::table('email_training_data')->insertGetId([
                'subject' => $request->input('subject'),
                'content' => $request->input('content'),
                'email_type' => $request->input('email_type'),
                'sender_name' => $request->input('sender_name', 'User'),
                'recipient' => $request->input('recipient', ''),
                'metadata' => json_encode([
                    'uploaded_via' => 'web_interface',
                    'upload_timestamp' => now()
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Analyze the email
            $analysis = $this->learningService->analyzeEmailContent(
                $request->input('content'),
                $request->input('email_type'),
                $trainingId
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'training_id' => $trainingId,
                    'analysis' => $analysis,
                    'message' => 'Email uploaded and analyzed successfully'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get learned templates
     */
    public function getLearnedTemplates(): JsonResponse
    {
        try {
            $templates = DB::table('email_templates')
                ->select([
                    'id',
                    'email_type',
                    'template_content',
                    'learned_patterns',
                    'confidence_score',
                    'created_at'
                ])
                ->orderBy('confidence_score', 'desc')
                ->get()
                ->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'email_type' => $template->email_type,
                        'template_preview' => substr($template->template_content, 0, 150) . '...',
                        'patterns' => json_decode($template->learned_patterns, true),
                        'confidence' => $template->confidence_score,
                        'created_at' => $template->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'templates' => $templates,
                    'total_count' => $templates->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get generation history
     */
    public function getHistory(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 20);
            $offset = $request->input('offset', 0);

            $history = DB::table('sprint_email_history')
                ->select([
                    'id',
                    'user_request',
                    'generated_subject',
                    'generated_content',
                    'template_used',
                    'ai_confidence',
                    'user_feedback',
                    'created_at'
                ])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'request' => $item->user_request,
                        'subject' => $item->generated_subject,
                        'content_preview' => substr($item->generated_content, 0, 200) . '...',
                        'template_used' => $item->template_used,
                        'confidence' => $item->ai_confidence,
                        'feedback' => $item->user_feedback ? json_decode($item->user_feedback, true) : null,
                        'created_at' => $item->created_at
                    ];
                });

            $total = DB::table('sprint_email_history')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'history' => $history,
                    'pagination' => [
                        'total' => $total,
                        'limit' => $limit,
                        'offset' => $offset,
                        'has_more' => ($offset + $limit) < $total
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit feedback for a generated email
     */
    public function submitFeedback(Request $request, int $historyId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'sometimes|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid input',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $exists = DB::table('sprint_email_history')->where('id', $historyId)->exists();

            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'error' => 'Email history not found'
                ], 404);
            }

            DB::table('sprint_email_history')
                ->where('id', $historyId)
                ->update([
                    'user_feedback' => json_encode([
                        'rating' => $request->input('rating'),
                        'feedback' => $request->input('feedback', ''),
                        'timestamp' => now()
                    ]),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Feedback submitted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to submit feedback: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_training_emails' => DB::table('email_training_data')->count(),
                'total_templates' => DB::table('email_templates')->count(),
                'total_generated' => DB::table('sprint_email_history')->count(),
                'templates_by_type' => DB::table('email_templates')
                    ->select('email_type', DB::raw('COUNT(*) as count'))
                    ->groupBy('email_type')
                    ->pluck('count', 'email_type'),
                'average_confidence' => DB::table('email_templates')->avg('confidence_score'),
                'recent_activity' => DB::table('sprint_email_history')
                    ->where('created_at', '>', now()->subDays(7))
                    ->count(),
                'feedback_summary' => $this->getFeedbackSummary()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get feedback summary for statistics
     */
    protected function getFeedbackSummary(): array
    {
        $feedbackData = DB::table('sprint_email_history')
            ->whereNotNull('user_feedback')
            ->pluck('user_feedback')
            ->map(function ($feedback) {
                return json_decode($feedback, true);
            })
            ->filter();

        if ($feedbackData->isEmpty()) {
            return [
                'total_feedback' => 0,
                'average_rating' => 0,
                'rating_distribution' => []
            ];
        }

        $ratings = $feedbackData->pluck('rating');
        $ratingDistribution = $ratings->countBy()->toArray();

        return [
            'total_feedback' => $feedbackData->count(),
            'average_rating' => $ratings->avg(),
            'rating_distribution' => $ratingDistribution
        ];
    }
}