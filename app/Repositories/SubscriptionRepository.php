<?php
namespace App\Repositories;

use App\Contracts\SubscriptionRepositoryInterface;
use App\Enums\SubcriptionPlan\PlanBillingCycle;
use App\Enums\SubcriptionPlan\SubcriptionStatus;
use App\Models\User;
use App\Models\UserSubscription\Plan;
use App\Models\UserSubscription\Subscription;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function findActiveByUser(User $user): ?Subscription
    {
        return $user->subscriptions()
                   ->where('status', SubcriptionStatus::ACTIVE->value)
                   ->where(function ($query) {
                       $query->whereNull('ends_at')
                             ->orWhere('ends_at', '>=', now());
                   })
                   ->first();
    }

    public function create(User $user, Plan $plan, array $data = []): Subscription
    {
        $defaultData = [
            'starts_at' => now(),
            'ends_at' => $plan->billing_cycle === PlanBillingCycle::LIFETIME ? null : now()->addMonth(),
        ];

        return $user->subscriptions()->create(array_merge($defaultData, [
            'plan_id' => $plan->id,
        ], $data));
    }

    public function suspend(Subscription $subscription): bool
    {
        $subscription->suspend();
        return true;
    }

    public function resume(Subscription $subscription): bool
    {
        $subscription->resume();
        return true;
    }

    public function cancel(Subscription $subscription): bool
    {
        return $subscription->update(['status' => SubcriptionStatus::CANCELED->value]);
    }
}
