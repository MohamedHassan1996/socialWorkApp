<?php

namespace App\Models\UserSubscription;

use App\Enums\IsActive;
use App\Enums\SubcriptionPlan\PlanBillingCycle;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Plan extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'key', 'name', 'description', 'price', 'billing_cycle', 'is_active', 'is_popular'/*, 'available_from', 'available_until'*/
    ];

   protected function casts(): array
   {
       return [
           'price' => 'decimal:2',
           'billing_cycle' => PlanBillingCycle::class,
           'is_active' => IsActive::class,
           'is_popular' => IsActive::class
       ];
   }

   public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
                    ->withPivot('limit_value')
                    ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFeatureLimit(string $featureKey): ?int
    {
        $feature = $this->features()->where('key', $featureKey)->first();
        return $feature ? $feature->pivot->limit_value : null;
    }

    public function hasFeature(string $featureKey): bool
    {
        $limit = $this->getFeatureLimit($featureKey);
        return $limit !== null && $limit !== 0;
    }

    public function hasUnlimitedFeature(string $featureKey): bool
    {
        return $this->getFeatureLimit($featureKey) === -1;
    }

    public function isHigherThan(Plan $otherPlan): bool
    {
        $hierarchy = ['free' => 0, 'pro' => 1, 'premium' => 2, 'enterprise' => 3];
        return ($hierarchy[$this->key] ?? 0) > ($hierarchy[$otherPlan->key] ?? 0);
    }

    public function canUpgradeTo(Plan $targetPlan): bool
    {
        return $targetPlan->isHigherThan($this);
    }

    public function canDowngradeTo(Plan $targetPlan): bool
    {
        return $this->isHigherThan($targetPlan);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', IsActive::ACTIVE->value);
    }

    public function isFree(): bool
    {
        return $this->price == 0;
    }

    public function isPaid(): bool
    {
        return $this->price > 0;
    }

    public function isLifetime(): bool
    {
        return $this->billing_cycle === PlanBillingCycle::LIFETIME;
    }

}
