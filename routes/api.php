<?php

use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SprintEmailController;

Route::prefix('v1')->name('api.')->group(function () {
    Route::prefix('email')->name('email.')->group(function () {
        // Change 'draft' name to 'generate' to match frontend
        Route::post('/draft', [EmailController::class, 'draft'])->name('generate');
        Route::post('/response', [EmailController::class, 'generateResponse'])->name('response');
        Route::post('/analyze', [EmailController::class, 'analyze'])->name('analyze');
        Route::post('/summarize', [EmailController::class, 'summarize'])->name('summarize');
        Route::post('/template', [EmailController::class, 'generateTemplate'])->name('template');
        Route::get('/history', [EmailController::class, 'history'])->name('history');
        Route::post('/history/{id}/rate', [EmailController::class, 'rate'])->name('rate');
    });

    Route::prefix('sprint')->name('sprint.')->group(function () {
        Route::post('/generate', [SprintEmailController::class, 'generateEmail'])->name('generate');

        Route::post('/upload-training', [SprintEmailController::class, 'uploadTrainingEmail'])->name('upload-training');
        Route::get('/templates', [SprintEmailController::class, 'getLearnedTemplates'])->name('templates');

        Route::get('/history', [SprintEmailController::class, 'getHistory'])->name('history');
        Route::post('/feedback/{historyId}', [SprintEmailController::class, 'submitFeedback'])->name('feedback');

        Route::get('/stats', [SprintEmailController::class, 'getStats'])->name('stats');

        Route::get('/health', function () {
            return response()->json([
                'status' => 'healthy',
                'service' => 'Sprint Email AI System',
                'timestamp' => now(),
                'database' => \Illuminate\Support\Facades\DB::connection()->getPdo() ? 'connected' : 'disconnected'
            ]);
        })->name('health');
    });
});