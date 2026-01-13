<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\EmailLearningService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application Services.
     */
    public function register(): void
    {
        $this->app->singleton(EmailLearningService::class);
    }

    /**
     * Bootstrap any application Services.
     */
    public function boot(): void
    {
    }
}
