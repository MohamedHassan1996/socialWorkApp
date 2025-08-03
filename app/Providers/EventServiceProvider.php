<?php

namespace App\Providers;

use App\Events\Subscription\FeatureUsageRecorded;
use App\Events\Subscription\SubscriptionCreated;
use App\Events\Subscription\SubscriptionResumed;
use App\Listeners\Subscription\ResetUserFeatureUsage;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SubscriptionCreated::class => [
            ResetUserFeatureUsage::class,
            //SendWelcomeEmail::class,
        ],

        // SubscriptionSuspended::class => [
        //     // Add listeners for suspension
        // ],

        SubscriptionResumed::class => [
            // Add listeners for resumption
        ],

        FeatureUsageRecorded::class => [
            // Add listeners for usage tracking
        ],
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
