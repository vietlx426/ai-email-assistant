<?php

namespace App\Http\Controllers;

use App\Actions\DraftEmailAction;
use App\Actions\GenerateResponseAction;
use App\Actions\AnalyzeEmailAction;
use App\Actions\SummarizeEmailAction;
use App\Actions\GenerateTemplateAction;
use App\Contracts\EmailHistoryRepositoryInterface;
use App\DTOs\EmailDraftRequest as EmailDraftDTO;
use App\DTOs\EmailResponseRequest as EmailResponseDTO;
use App\DTOs\EmailAnalysisRequest as EmailAnalysisDTO;
use App\DTOs\EmailSummaryRequest as EmailSummaryDTO;
use App\DTOs\TemplateGenerationRequest as TemplateGenerationDTO;
use App\Http\Requests\DraftEmailRequest;
use App\Http\Requests\GenerateResponseRequest;
use App\Http\Requests\AnalyzeEmailRequest;
use App\Http\Requests\SummarizeEmailRequest;
use App\Http\Requests\GenerateTemplateRequest;
use App\Http\Requests\RateEmailRequest;
use App\Http\Resources\EmailHistoryResource;
use App\Http\Resources\EmailResultResource;
use Illuminate\Http\JsonResponse;

class EmailController extends Controller
{
    /**
     * Draft a new email
     */
    public function draft(DraftEmailRequest $request, DraftEmailAction $action): JsonResponse
    {
        $dto = EmailDraftDTO::fromArray($request->validated());

        $result = $action->execute($dto);

        return response()->json(
            new EmailResultResource($result),
            $result->success ? 200 : 422
        );
    }

    /**
     * Generate email response
     */
    public function generateResponse(
        GenerateResponseRequest $request,
        GenerateResponseAction $action
    ): JsonResponse {
        $dto = EmailResponseDTO::fromArray($request->validated());

        $result = $action->execute($dto);

        return response()->json(
            new EmailResultResource($result),
            $result->success ? 200 : 422
        );
    }

    /**
     * Analyze email
     */
    public function analyze(
        AnalyzeEmailRequest $request,
        AnalyzeEmailAction $action
    ): JsonResponse {
        $dto = EmailAnalysisDTO::fromArray($request->validated());

        $result = $action->execute($dto);

        return response()->json(
            new EmailResultResource($result),
            $result->success ? 200 : 422
        );
    }

    /**
     * Summarize email thread
     */
    public function summarize(
        SummarizeEmailRequest $request,
        SummarizeEmailAction $action
    ): JsonResponse {
        $dto = EmailSummaryDTO::fromArray($request->validated());

        $result = $action->execute($dto);

        return response()->json(
            new EmailResultResource($result),
            $result->success ? 200 : 422
        );
    }

    /**
     * Generate email template
     */
    public function generateTemplate(
        GenerateTemplateRequest $request,
        GenerateTemplateAction $action
    ): JsonResponse {
        $dto = TemplateGenerationDTO::fromArray($request->validated());

        $result = $action->execute($dto);

        return response()->json(
            new EmailResultResource($result),
            $result->success ? 200 : 422
        );
    }

    /**
     * Get email history
     */
    public function history(EmailHistoryRepositoryInterface $repository): JsonResponse
    {
        $history = $repository->getRecent(20);

        return response()->json([
            'success' => true,
            'data' => EmailHistoryResource::collection($history),
        ]);
    }

    /**
     * Rate an email generation
     */
    public function rate(
        int $id,
        RateEmailRequest $request,
        EmailHistoryRepositoryInterface $repository
    ): JsonResponse {
        $validated = $request->validated();

        $updated = $repository->updateRating(
            $id,
            $validated['rating'],
            $validated['feedback'] ?? null
        );

        return response()->json([
            'success' => $updated,
            'message' => $updated ? 'Thank you for your feedback!' : 'Failed to update rating.',
        ]);
    }
}