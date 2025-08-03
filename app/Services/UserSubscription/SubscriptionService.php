<?php

namespace App\Services\UserSubscription;

use App\Models\User;
use App\Models\UserSubscription\Plan;
use App\Models\UserSubscription\Subscription;
// use App\Events\SubscriptionCreated;
// use App\Events\SubscriptionSuspended;
// use App\Events\SubscriptionResumed;

class SubscriptionService
{
    public function __construct(
        private FeatureAccessService $featureAccessService
    ) {}

    /**
     * Create a new subscription for a user
     */
    public function subscribe(User $user, Plan $plan, array $options = []): Subscription
    {
         if ($currentSubscription = $user->subscription) {
                $this->cancel($currentSubscription, 'Upgraded to new plan');
            }

            $subscription = $this->createSubscription($user, $plan, $options);

            // Reset feature usages for new subscription
            $this->resetUserFeatureUsages($user);

            //event(new SubscriptionCreated($subscription));

            return $subscription->load('plan');
    }

    /**
     * Upgrade user to a higher plan
     */
    public function upgrade(User $user, Plan $newPlan, array $options = []): Subscription
    {
        $currentSubscription = $user->subscription;
        $currentPlan = $currentSubscription?->plan;

        if (!$currentPlan || !$currentPlan->canUpgradeTo($newPlan)) {
            throw new \InvalidArgumentException('Cannot upgrade to this plan');
        }

         // Create new subscription
        $newSubscription = $this->createSubscription($user, $newPlan, $options);

        // Cancel old subscription
        if ($currentSubscription) {
            $this->cancel($currentSubscription, 'Upgraded to ' . $newPlan->name);
        }

        // Preserve existing feature usage for upgrade
        $this->migrateFeatureUsages($user, $currentSubscription->plan, $newPlan);

        //event(new SubscriptionUpgraded($newSubscription, $currentSubscription));

        return $newSubscription->load('plan');
    }

    /**
     * Downgrade user to a lower plan
     */
    public function downgrade(User $user, Plan $newPlan, array $options = []): Subscription
    {
        $currentSubscription = $user->subscription;
        $currentPlan = $currentSubscription?->plan;

        if (!$currentPlan || !$currentPlan->canDowngradeTo($newPlan)) {
            throw new \InvalidArgumentException('Cannot downgrade to this plan');
        }

        // Validate downgrade compatibility
        $this->validateDowngrade($user, $currentPlan, $newPlan);

        // Create new subscription
        $newSubscription = $this->createSubscription($user, $newPlan, $options);

        // Cancel old subscription
        if ($currentSubscription) {
            $this->cancel($currentSubscription, 'Downgraded to ' . $newPlan->name);
        }

        // Adjust feature usages for downgrade
        $this->adjustFeatureUsagesForDowngrade($user, $currentPlan, $newPlan);

        //event(new SubscriptionDowngraded($newSubscription, $currentSubscription));

        return $newSubscription->load('plan');
    }

    /**
     * Suspend a subscription
     */
    public function suspend(Subscription $subscription): bool
    {
        if (!$subscription->isActive()) {
            return false;
        }

        $subscription->suspend();
        //event(new SubscriptionSuspended($subscription));

        return true;
    }

    /**
     * Resume a suspended subscription
     */
    public function resume(Subscription $subscription): bool
    {
        if (!$subscription->isSuspended()) {
            return false;
        }

        $subscription->resume();
        //event(new SubscriptionResumed($subscription));

        return true;
    }

    /**
     * Cancel a subscription
     */
    public function cancel(Subscription $subscription): bool
    {
        $subscription->cancel();
        //event(new SubscriptionCancelled($subscription));

        return true;
    }

    /**
     * Start a trial subscription
     */
    public function startTrial(User $user, Plan $plan, int $trialDays = 14): Subscription
    {
        $subscription = $this->createSubscription($user, $plan, [
            'status' => 'trial',
            'trial_ends_at' => now()->addDays($trialDays),
        ]);

        //event(new SubscriptionCreated($subscription));

        return $subscription->load('plan');

    }

    /**
     * Convert trial to paid subscription
     */
    public function convertTrial(Subscription $subscription, array $paymentData = []): Subscription
    {
        if (!$subscription->isOnTrial()) {
            throw new \InvalidArgumentException('Subscription is not on trial');
        }

        $subscription->update([
            'status' => 'active',
            'payment_method' => $paymentData['payment_method'] ?? null,
            'external_id' => $paymentData['external_id'] ?? null,
            'ends_at' => $this->calculateEndDate($subscription->plan),
        ]);

        return $subscription->refresh();
    }

    /**
     * Get subscription analytics for a user
     */
    public function getSubscriptionAnalytics(User $user): array
    {
        $currentSubscription = $user->subscription;
        $history = $user->subscriptionHistory()->limit(10)->get();

        return [
            'current_subscription' => $currentSubscription,
            'plan_history' => $history,
            'subscription_duration' => $currentSubscription ?
                $currentSubscription->starts_at->diffInDays(now()) : 0,
            'upgrades_count' => $history->where('action', 'upgraded')->count(),
            'downgrades_count' => $history->where('action', 'downgraded')->count(),
            'cancellations_count' => $history->where('action', 'cancelled')->count(),
            'total_revenue' => $this->calculateUserRevenue($user),
            'next_billing_date' => $currentSubscription?->ends_at,
            'days_until_renewal' => $currentSubscription?->getRemainingDays(),
        ];
    }

    // Private helper methods
    private function createSubscription(User $user, Plan $plan, array $options = []): Subscription
    {
        $defaultOptions = [
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => $this->calculateEndDate($plan),
        ];

        $subscriptionData = array_merge($defaultOptions, $options, [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        return Subscription::create($subscriptionData);
    }

    private function calculateEndDate(Plan $plan): ?\Carbon\Carbon
    {
        return match($plan->billing_cycle) {
            'monthly' => now()->addMonth(),
            'yearly' => now()->addYear(),
            'lifetime' => null,
            default => now()->addMonth(),
        };
    }

    private function resetUserFeatureUsages(User $user): void
    {
        $user->featureUsages()->delete();
    }

    private function migrateFeatureUsages(User $user, Plan $oldPlan, Plan $newPlan): void
    {
        // Keep existing usage but ensure it doesn't exceed new limits
        $features = $newPlan->features;

        foreach ($features as $feature) {
            $newLimit = $feature->pivot->limit_value;
            if ($newLimit > 0) { // Not unlimited
                $user->featureUsages()
                     ->whereHas('feature', fn($q) => $q->where('key', $feature->key))
                     ->where('usage_count', '>', $newLimit)
                     ->update(['usage_count' => $newLimit]);
            }
        }
    }

    private function validateDowngrade(User $user, Plan $currentPlan, Plan $newPlan): void
    {
        $violations = [];

        // Check each feature limit
        foreach ($newPlan->features as $feature) {
            $newLimit = $feature->pivot->limit_value;
            $currentUsage = $this->featureAccessService->getUsageInCurrentPeriod($user, $feature->key);

            if ($newLimit > 0 && $currentUsage > $newLimit) {
                $violations[] = [
                    'feature' => $feature->key,
                    'current_usage' => $currentUsage,
                    'new_limit' => $newLimit,
                ];
            }
        }

        if (!empty($violations)) {
            throw new \InvalidArgumentException('Cannot downgrade: Current usage exceeds new plan limits', 0, $violations);
        }
    }

    private function adjustFeatureUsagesForDowngrade(User $user, Plan $oldPlan, Plan $newPlan): void
    {
        // Adjust usage to fit new limits
        foreach ($newPlan->features as $feature) {
            $newLimit = $feature->pivot->limit_value;
            if ($newLimit > 0) { // Not unlimited, not disabled
                $user->featureUsages()
                     ->whereHas('feature', fn($q) => $q->where('key', $feature->key))
                     ->where('usage_count', '>', $newLimit)
                     ->update(['usage_count' => $newLimit]);
            }
        }
    }

    private function calculateUserRevenue(User $user): float
    {
        return $user->subscriptions()
                   ->where('status', '!=', 'trial')
                   ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                   ->sum('plans.price');
    }
}
