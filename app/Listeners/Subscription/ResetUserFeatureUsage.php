<?php

namespace App\Listeners\Subscription;

use App\Events\Subscription\SubscriptionCreated;
use App\Models\UserSubscription\FeatureUsage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ResetUserFeatureUsage
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubscriptionCreated $event): void
    {
        // Reset feature usage when user subscribes to new plan
        FeatureUsage::where('user_id', $event->subscription->user_id)->delete();
    }
}
