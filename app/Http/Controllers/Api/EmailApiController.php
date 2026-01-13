<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailApiController extends Controller
{
    protected AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient' => 'required|email',
            'subject' => 'required|string|max:255',
            'instructions' => 'required|string|max:5000',
            'tone' => 'required|string|in:professional,friendly,formal,casual,urgent,apologetic'
        ]);

        try {
            $content = $this->aiService->generateEmail(
                $validated['instructions'],
                $validated['tone']
            );

            return response()->json([
                'success' => true,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate email. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function generateResponse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'originalEmail' => 'required|string|max:10000',
            'context' => 'nullable|string|max:2000',
            'tone' => 'required|string|in:professional,friendly,formal,concise,detailed'
        ]);

        try {
            $content = $this->aiService->generateReply(
                $validated['originalEmail'],
                $validated['context'] ?? ''
            );

            return response()->json([
                'success' => true,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate response. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:10000'
        ]);

        try {
            $analysis = $this->aiService->analyzeEmail($validated['content']);

            return response()->json([
                'success' => true,
                'analysis' => $analysis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze email. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function summarize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'thread' => 'required|string|max:50000'
        ]);

        try {
            $summary = $this->aiService->summarizeThread($validated['thread']);

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to summarize thread. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}