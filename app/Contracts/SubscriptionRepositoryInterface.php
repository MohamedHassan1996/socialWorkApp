<?php

namespace App\Contracts;

use App\Models\User;
use App\Models\UserSubscription\Plan;
use App\Models\UserSubscription\Subscription;

interface SubscriptionRepositoryInterface
{
    public function findActiveByUser(User $user): ?Subscription;
    public function create(User $user, Plan $plan, array $data = []): Subscription;
    public function suspend(Subscription $subscription): bool;
    public function resume(Subscription $subscription): bool;
    public function cancel(Subscription $subscription): bool;
}
