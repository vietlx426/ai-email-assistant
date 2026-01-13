<?php

namespace App\Providers;

use App\Contracts\AIProviderInterface;
use App\Contracts\CacheServiceInterface;
use App\Contracts\EmailHistoryRepositoryInterface;
use App\Contracts\EmailTemplateRepositoryInterface;
use App\Repositories\EmailHistoryRepository;
use App\Repositories\EmailTemplateRepository;
use App\Services\CacheService;
use App\Services\DeepSeekProvider;
use App\Services\PromptService;
use Illuminate\Support\ServiceProvider;
use App\Services\EmailLearningService;
use App\Services\SprintEmailGenerationService;

class EmailAssistantServiceProvider extends ServiceProvider
{
    /**
     * Register Services.
     */
    public function register(): void
    {
        $this->app->bind(AIProviderInterface::class, DeepSeekProvider::class);
        $this->app->bind(EmailHistoryRepositoryInterface::class, EmailHistoryRepository::class);
        $this->app->bind(EmailTemplateRepositoryInterface::class, EmailTemplateRepository::class);
        $this->app->bind(CacheServiceInterface::class, CacheService::class);

        $this->app->singleton(PromptService::class);
        $this->app->singleton(DeepSeekProvider::class, function ($app) {
            return new DeepSeekProvider();
        });
        $this->app->singleton(SprintEmailGenerationService::class, function ($app) {
            return new SprintEmailGenerationService(
                $app->make(DeepSeekProvider::class),
                $app->make(EmailLearningService::class)
            );
        });
    }

    /**
     * Bootstrap Services.
     */
    public function boot(): void
    {
    }
}