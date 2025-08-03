<?php
namespace App\Repositories;

use App\Enums\SubcriptionPlan\PlanBillingCycle;
use App\Models\User;
use App\Models\UserSubscription\Feature;
use App\Models\UserSubscription\FeatureUsage;
use Carbon\Carbon;

class FeatureUsageRepository
{
    public function getCurrentPeriodUsage(User $user, Feature $feature, ?string $scopeType = null, ?int $scopeId = null): ?FeatureUsage
    {
        $periodStart = $this->getCurrentPeriodStart($user);

        $query = FeatureUsage::where('user_id', $user->id)
                           ->where('feature_id', $feature->id)
                           ->where('period_start', $periodStart);

        if ($scopeType && $scopeId) {
            $query->where('scope_type', $scopeType)
                  ->where('scope_id', $scopeId);
        } elseif (!$scopeType && !$scopeId) {
            $query->whereNull('scope_type')
                  ->whereNull('scope_id');
        }

        return $query->first();
    }

    /**
     * Get total usage across all scopes for a feature
     */
    public function getTotalUsageAcrossScopes(User $user, Feature $feature): int
    {
        $periodStart = $this->getCurrentPeriodStart($user);

        return FeatureUsage::where('user_id', $user->id)
                          ->where('feature_id', $feature->id)
                          ->where('period_start', $periodStart)
                          ->sum('usage_count');
    }

    /**
     * Get usage breakdown by scope (e.g., per workspace)
     */
    public function getUsageByScope(User $user, Feature $feature, string $scopeType): array
    {
        $periodStart = $this->getCurrentPeriodStart($user);

        return FeatureUsage::where('user_id', $user->id)
                          ->where('feature_id', $feature->id)
                          ->where('period_start', $periodStart)
                          ->where('scope_type', $scopeType)
                          ->get()
                          ->groupBy('scope_id')
                          ->map(function ($usages) {
                              return $usages->sum('usage_count');
                          })
                          ->toArray();
    }

    /**
     * Increment usage for a feature with optional scope
     */
    public function incrementUsage(User $user, Feature $feature, ?string $scopeType = null, ?int $scopeId = null, int $amount = 1, array $metadata = []): FeatureUsage
    {
        $periodStart = $this->getCurrentPeriodStart($user);
        $periodEnd = $this->getCurrentPeriodEnd($user);

        $attributes = [
            'user_id' => $user->id,
            'feature_id' => $feature->id,
            'period_start' => $periodStart,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
        ];

        $values = [
            'period_end' => $periodEnd,
            'metadata' => $metadata,
        ];

        // First, try to find existing usage record
        $usage = FeatureUsage::where($attributes)->first();

        if ($usage) {
            // Update existing record
            $usage->increment('usage_count', $amount);

            // Merge metadata if provided
            if (!empty($metadata)) {
                $existingMetadata = $usage->metadata ?? [];
                $updatedMetadata = array_merge($existingMetadata, $metadata);
                $usage->update(['metadata' => $updatedMetadata]);
            }
        } else {
            // Create new record
            $usage = FeatureUsage::create(array_merge($attributes, $values, [
                'usage_count' => $amount
            ]));
        }

        return $usage->fresh();
    }

    /**
     * Decrement usage for a feature with optional scope
     */
    public function decrementUsage(User $user, Feature $feature, ?string $scopeType = null, ?int $scopeId = null, int $amount = 1): bool
    {
        $periodStart = $this->getCurrentPeriodStart($user);

        $query = FeatureUsage::where('user_id', $user->id)
                           ->where('feature_id', $feature->id)
                           ->where('period_start', $periodStart);

        if ($scopeType && $scopeId) {
            $query->where('scope_type', $scopeType)
                  ->where('scope_id', $scopeId);
        } elseif (!$scopeType && !$scopeId) {
            $query->whereNull('scope_type')
                  ->whereNull('scope_id');
        }

        $usage = $query->first();

        if ($usage && $usage->usage_count >= $amount) {
            $usage->decrement('usage_count', $amount);

            // Update metadata to track decrements
            $metadata = $usage->metadata ?? [];
            $metadata['decrements'] = $metadata['decrements'] ?? [];
            $metadata['decrements'][] = [
                'amount' => $amount,
                'timestamp' => now()->toISOString(),
                'action' => 'decremented',
            ];
            $usage->update(['metadata' => $metadata]);

            return true;
        }

        return false;
    }

    /**
     * Reset usage for a specific feature and scope
     */
    public function resetUsage(User $user, Feature $feature, ?string $scopeType = null, ?int $scopeId = null): bool
    {
        $periodStart = $this->getCurrentPeriodStart($user);

        $query = FeatureUsage::where('user_id', $user->id)
                           ->where('feature_id', $feature->id)
                           ->where('period_start', $periodStart);

        if ($scopeType && $scopeId) {
            $query->where('scope_type', $scopeType)
                  ->where('scope_id', $scopeId);
        } elseif (!$scopeType && !$scopeId) {
            $query->whereNull('scope_type')
                  ->whereNull('scope_id');
        }

        return $query->delete() > 0;
    }

    /**
     * Get usage in a specific time period
     */
    public function getUsageInPeriod(User $user, Feature $feature, Carbon $start, Carbon $end, ?string $scopeType = null, ?int $scopeId = null): int
    {
        $query = FeatureUsage::where('user_id', $user->id)
                           ->where('feature_id', $feature->id)
                           ->whereBetween('period_start', [$start, $end]);

        if ($scopeType && $scopeId) {
            $query->where('scope_type', $scopeType)
                  ->where('scope_id', $scopeId);
        } elseif (!$scopeType && !$scopeId) {
            $query->whereNull('scope_type')
                  ->whereNull('scope_id');
        }

        return $query->sum('usage_count');
    }

    /**
     * Get detailed usage statistics for a user
     */
    public function getUserUsageStats(User $user): array
    {
        $periodStart = $this->getCurrentPeriodStart($user);

        $usages = FeatureUsage::where('user_id', $user->id)
                            ->where('period_start', $periodStart)
                            ->with('feature')
                            ->get();

        $stats = [
            'total_features_used' => $usages->groupBy('feature_id')->count(),
            'total_usage_count' => $usages->sum('usage_count'),
            'by_feature' => [],
            'by_scope' => [],
        ];

        // Group by feature
        $byFeature = $usages->groupBy('feature.key');
        foreach ($byFeature as $featureKey => $featureUsages) {
            $stats['by_feature'][$featureKey] = [
                'feature_name' => $featureUsages->first()->feature->name,
                'total_usage' => $featureUsages->sum('usage_count'),
                'scope_breakdown' => $featureUsages->groupBy('scope_type')->map(function ($scopeUsages) {
                    return $scopeUsages->sum('usage_count');
                })->toArray(),
            ];
        }

        // Group by scope
        $byScope = $usages->whereNotNull('scope_type')->groupBy('scope_type');
        foreach ($byScope as $scopeType => $scopeUsages) {
            $stats['by_scope'][$scopeType] = [
                'total_usage' => $scopeUsages->sum('usage_count'),
                'unique_scopes' => $scopeUsages->groupBy('scope_id')->count(),
                'breakdown' => $scopeUsages->groupBy('scope_id')->map(function ($scopeItems) {
                    return $scopeItems->sum('usage_count');
                })->toArray(),
            ];
        }

        return $stats;
    }

    /**
     * Get workspace-specific usage breakdown
     */
    public function getWorkspaceUsageBreakdown(User $user): array
    {
        $periodStart = $this->getCurrentPeriodStart($user);

        $workspaceUsages = FeatureUsage::where('user_id', $user->id)
                                     ->where('period_start', $periodStart)
                                     ->where('scope_type', 'workspace')
                                     ->with('feature')
                                     ->get();

        $breakdown = [];

        foreach ($workspaceUsages->groupBy('scope_id') as $workspaceId => $usages) {
            $breakdown[$workspaceId] = [
                'workspace_id' => $workspaceId,
                'total_usage' => $usages->sum('usage_count'),
                'features' => $usages->groupBy('feature.key')->map(function ($featureUsages, $featureKey) {
                    return [
                        'feature_key' => $featureKey,
                        'feature_name' => $featureUsages->first()->feature->name,
                        'usage_count' => $featureUsages->sum('usage_count'),
                    ];
                })->values()->toArray(),
            ];
        }

        return $breakdown;
    }

    /**
     * Clean up old usage records
     */
    public function cleanupOldUsage(Carbon $before): int
    {
        return FeatureUsage::where('period_end', '<', $before)->delete();
    }

    /**
     * Get usage trends over time
     */
    public function getUsageTrends(User $user, int $months = 6): array
    {
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $monthlyUsage = FeatureUsage::where('user_id', $user->id)
                                      ->whereBetween('period_start', [$monthStart, $monthEnd])
                                      ->with('feature')
                                      ->get()
                                      ->groupBy('feature.key')
                                      ->map(function ($usages) {
                                          return $usages->sum('usage_count');
                                      });

            $trends[] = [
                'month' => $month->format('Y-m'),
                'month_name' => $month->format('M Y'),
                'usage' => $monthlyUsage->toArray(),
                'total_usage' => $monthlyUsage->sum(),
            ];
        }

        return $trends;
    }

    // Helper methods for period calculation
    private function getCurrentPeriodStart(User $user): Carbon
    {
        $subscription = $user->subscription;

        if (!$subscription) {
            return now()->startOfMonth();
        }

        return match($subscription->plan->billing_cycle) {
            PlanBillingCycle::MONTHLY => now()->startOfMonth(),
            PlanBillingCycle::YEARLY => now()->startOfYear(),
            PlanBillingCycle::LIFETIME => $subscription->starts_at->startOfMonth(),
            default => now()->startOfMonth(),
        };
    }

    private function getCurrentPeriodEnd(User $user): Carbon
    {
        $subscription = $user->subscription;

        if (!$subscription) {
            return now()->endOfMonth();
        }

        return match($subscription->plan->billing_cycle) {
            PlanBillingCycle::MONTHLY => now()->endOfMonth(),
            PlanBillingCycle::YEARLY => now()->endOfYear(),
            PlanBillingCycle::LIFETIME => now()->endOfMonth(), // For lifetime, we still track monthly usage
            default => now()->endOfMonth(),
        };
    }
}
