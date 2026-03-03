<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Observers\BlogPostObserver;
use App\Services\BlogAnalyticsService;
use App\Services\BlogCacheService;
use App\Services\SchemaGenerator;
use App\Services\SeoScoreCalculator;
use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register services as singletons
        $this->app->singleton(SeoScoreCalculator::class);
        $this->app->singleton(BlogAnalyticsService::class);
        $this->app->singleton(BlogCacheService::class);
        $this->app->singleton(SchemaGenerator::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register model observers
        BlogPost::observe(BlogPostObserver::class);
    }
}
