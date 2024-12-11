<?php

namespace App\Providers;

use App\Services\ActivityLoggerService;
use Illuminate\Support\ServiceProvider;

class ActivityLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ActivityLoggerService::class, function ($app) {
            return new ActivityLoggerService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // If you need any additional bootstrapping for this service
    }
}