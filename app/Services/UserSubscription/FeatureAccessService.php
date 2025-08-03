<?php

namespace App\Services\UserSubscription;

use App\Models\User;
use App\Models\UserSubscription\FeatureUsage;
use App\Repositories\FeatureRepository;
use App\Repositories\FeatureUsageRepository;

class FeatureAccessService{

    public function __construct(
        private FeatureRepository $featureRepository,
        private FeatureUsageRepository $featureUsageRepository
    ) {}

    public function canUseFeature(
        User $user,
        string $featureKey,
        ?string $scopeType = null,
        ?int $scopeId = null,
        int $requestedAmount = 1
    ): bool {
        return $this->checkFeatureAccess($user, $featureKey, $scopeType, $scopeId, $requestedAmount);
    }

    public function getRemainingUsage(
        User $user,
        string $featureKey,
        ?string $scopeType = null,
        ?int $scopeId = null
    ): ?int {
        $plan = $user->getCurrentPlan();
        $feature = $this->featureRepository->findByKey($featureKey);

        if (!$plan || !$feature || !$feature->isCountable()) {
            return null;
        }

        $limit = $plan->getFeatureLimit($featureKey);

        if ($limit === -1) {
            return PHP_INT_MAX; // Unlimited
        }

        if ($limit === null || $limit === 0) {
            return 0; // Not available
        }

        $currentUsage = $this->getUsageInCurrentPeriod($user, $featureKey, $scopeType, $scopeId);

        return max(0, $limit - $currentUsage);
    }

    public function recordUsage(
        User $user,
        string $featureKey,
        ?string $scopeType = null,
        ?int $scopeId = null,
        int $amount = 1,
        array $metadata = []
    ): bool {
        $feature = $this->featureRepository->findByKey($featureKey);

        if (!$feature || !$feature->isCountable()) {
            return false;
        }

        if (!$this->canUseFeature($user, $featureKey, $scopeType, $scopeId, $amount)) {
            //event(new FeatureLimitExceeded($user, $feature, $amount));
            return false;
        }

        $usage = $this->featureUsageRepository->incrementUsage(
            $user,
            $feature,
            $scopeType,
            $scopeId,
            $amount,
            array_merge($metadata, [
                'recorded_at' => now()->toISOString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
        );

        //event(new FeatureUsed($user, $feature, $amount, $scopeType, $scopeId));

        return true;
    }

    public function getUsageInCurrentPeriod(
        User $user,
        string $featureKey,
        ?string $scopeType = null,
        ?int $scopeId = null
    ): int {
        $feature = $this->featureRepository->findByKey($featureKey);

        if (!$feature) {
            return 0;
        }

        $usage = $this->featureUsageRepository->getCurrentPeriodUsage($user, $feature, $scopeType, $scopeId);

        return $usage?->usage_count ?? 0;
    }

    public function getTotalUsageAcrossScopes(User $user, string $featureKey): int
    {
        $feature = $this->featureRepository->findByKey($featureKey);

        if (!$feature) {
            return 0;
        }

        return $this->featureUsageRepository->getTotalUsageAcrossScopes($user, $feature);
    }

    /**
     * Get detailed usage breakdown for a user
     */
    public function getUsageBreakdown(User $user, array $features = null): array
    {
        $plan = $user->getCurrentPlan();
        if (!$plan) {
            return [];
        }

        $featuresToCheck = $features ?: $plan->features->pluck('key')->toArray();
        $breakdown = [];

        foreach ($featuresToCheck as $featureKey) {
            $feature = $this->featureRepository->findByKey($featureKey);
            if (!$feature) continue;

            $limit = $plan->getFeatureLimit($featureKey);
            $globalUsage = $this->getUsageInCurrentPeriod($user, $featureKey);
            $scopedUsage = $this->getScopedUsageBreakdown($user, $featureKey);

            $breakdown[$featureKey] = [
                'feature' => $feature->only(['key', 'name', 'type', 'unit']),
                'limit' => $limit,
                'global_usage' => $globalUsage,
                'scoped_usage' => $scopedUsage,
                'remaining' => $this->getRemainingUsage($user, $featureKey),
                'utilization_percentage' => $limit > 0 ? round(($globalUsage / $limit) * 100, 2) : 0,
                'is_unlimited' => $limit === -1,
                'is_available' => $limit !== null && $limit !== 0,
            ];
        }

        return $breakdown;
    }

    /**
     * Check if user is approaching any limits
     */
    public function getApproachingLimits(User $user, int $thresholdPercentage = 80): array
    {
        $breakdown = $this->getUsageBreakdown($user);
        $approaching = [];

        foreach ($breakdown as $featureKey => $data) {
            if ($data['is_unlimited'] || !$data['is_available']) {
                continue;
            }

            if ($data['utilization_percentage'] >= $thresholdPercentage) {
                $approaching[] = [
                    'feature_key' => $featureKey,
                    'feature_name' => $data['feature']['name'],
                    'utilization_percentage' => $data['utilization_percentage'],
                    'remaining' => $data['remaining'],
                    'limit' => $data['limit'],
                ];
            }
        }

        return $approaching;
    }

    /**
     * Reset usage for specific feature
     */
    public function resetFeatureUsage(User $user, string $featureKey, ?string $scopeType = null, ?int $scopeId = null): bool
    {
        $feature = $this->featureRepository->findByKey($featureKey);
        if (!$feature) {
            return false;
        }

        $query = FeatureUsage::where('user_id', $user->id)
                            ->where('feature_id', $feature->id);

        if ($scopeType && $scopeId) {
            $query->where('scope_type', $scopeType)->where('scope_id', $scopeId);
        } elseif (!$scopeType && !$scopeId) {
            $query->whereNull('scope_type')->whereNull('scope_id');
        }

        $deleted = $query->delete();

        return $deleted > 0;
    }

    // Private helper methods
    private function checkFeatureAccess(
        User $user,
        string $featureKey,
        ?string $scopeType,
        ?int $scopeId,
        int $requestedAmount
    ): bool {
        $plan = $user->getCurrentPlan();


        if (!$plan) {
            return false;
        }

        // Check if subscription is active
        if (!$user->hasActivePlan()) {
            return false;
        }


        $feature = $this->featureRepository->findByKey($featureKey);

        if (!$feature) {
            return false;
        }

        $limit = $plan->getFeatureLimit($featureKey);

        // Feature not available in plan
        if ($limit === null || $limit === 0) {
            return false;
        }

        // Unlimited access
        if ($limit === -1) {
            return true;
        }

        // Boolean feature
        if ($feature->isBoolean()) {
            return $limit === 1;
        }

        // Countable feature - check usage
        if ($feature->isCountable()) {
            $currentUsage = $this->getUsageInCurrentPeriod($user, $featureKey, $scopeType, $scopeId);
            return ($currentUsage + $requestedAmount) <= $limit;
        }

        return false;
    }

    private function getScopedUsageBreakdown(User $user, string $featureKey): array
    {
        $feature = $this->featureRepository->findByKey($featureKey);

        if (!$feature) {
            return [];
        }

        return $this->featureUsageRepository->getUsageByScope($user, $feature, 'workspace');
    }

}
