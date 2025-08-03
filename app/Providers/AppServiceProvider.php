<?php

namespace App\Providers;

use App\Contracts\FeatureAccessServiceInterface;
use App\Contracts\SubscriptionRepositoryInterface;
use App\Repositories\SubscriptionRepository;
use App\Services\UserSubscription\FeatureAccessService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(FeatureAccessServiceInterface::class, FeatureAccessService::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
