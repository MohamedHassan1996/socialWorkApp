<?php

namespace App\Models\UserSubscription;

use App\Models\User;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

use Illuminate\Database\Eloquent\Builder;

class FeatureUsage extends Model
{
    use CreatedUpdatedBy;
    protected $fillable = [
        'user_id', 'feature_id', 'usage_count', 'period_start', 'period_end', 'scope_type', 'scope_id',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function scopable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'scope_type', 'scope_id');
    }

    // Scopes
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForFeature(Builder $query, string $featureKey): Builder
    {
        return $query->whereHas('feature', function ($q) use ($featureKey) {
            $q->where('key', $featureKey);
        });
    }

    public function scopeForScope(Builder $query, string $scopeType, int $scopeId): Builder
    {
        return $query->where('scope_type', $scopeType)->where('scope_id', $scopeId);
    }

    public function scopeCurrentPeriod(Builder $query, string $periodStart): Builder
    {
        return $query->where('period_start', $periodStart);
    }

    public function scopeWorkspace(Builder $query, int $workspaceId): Builder
    {
        return $query->forScope('workspace', $workspaceId);
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('scope_type')->whereNull('scope_id');
    }

    // Utility methods
    public function isWorkspaceScoped(): bool
    {
        return $this->scope_type === 'workspace';
    }

    public function isGlobal(): bool
    {
        return $this->scope_type === null && $this->scope_id === null;
    }


}
