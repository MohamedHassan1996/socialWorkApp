<?php

namespace App\Models\UserSubscription;

use App\Enums\IsActive;
use App\Enums\SubcriptionPlan\PlanFeatureType;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Feature extends Model
{
    use CreatedUpdatedBy;
    protected $fillable = ['key', 'name', 'description', 'type', 'category', 'unit', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'type' => PlanFeatureType::class, // Assuming type is stored as a string, adjust if necessary
            'is_active' => IsActive::class
        ];
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
                    ->withPivot('limit_value')
                    ->withTimestamps();
    }

    public function usages(): HasMany
    {
        return $this->hasMany(FeatureUsage::class);
    }


    public function isCountable(): bool
    {
        return $this->type === PlanFeatureType::COUNTABLE;
    }

    public function isBoolean(): bool
    {
        return $this->type === PlanFeatureType::BOOLEAN;
    }

    public function isUnlimited(): bool
    {
        return $this->type === PlanFeatureType::UNLIMITED;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
