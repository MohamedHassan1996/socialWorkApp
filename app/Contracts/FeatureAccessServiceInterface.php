<?php

namespace App\Contracts;

use App\Models\User;

interface FeatureAccessServiceInterface
{
    public function canUseFeature(User $user, string $featureKey): bool;
    public function getRemainingUsage(User $user, string $featureKey): ?int;
    public function recordUsage(User $user, string $featureKey, int $amount = 1): bool;
    public function getUsageInCurrentPeriod(User $user, string $featureKey): int;
}
