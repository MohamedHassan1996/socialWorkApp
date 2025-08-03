<?php

namespace App\Models\UserSubscription;

use App\Enums\SubcriptionPlan\SubcriptionStatus;
use App\Models\User;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Subscription extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'user_id', 'plan_id', 'status', 'starts_at', 'ends_at', 'suspended_at'
    ];

    public function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'suspended_at' => 'datetime',
            'status' => SubcriptionStatus::class,
        ];
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status === SubcriptionStatus::ACTIVE &&
               $this->starts_at <= now() &&
               ($this->ends_at === null || $this->ends_at >= now());
    }

    public function isSuspended(): bool
    {
        return $this->status === SubcriptionStatus::SUSPENDED &&
               $this->suspended_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->status === SubcriptionStatus::EXPIRED ||
               ($this->ends_at !== null && $this->ends_at < now());
    }

    public function isCancelled(): bool
    {
        return $this->status === SubcriptionStatus::CANCELED;
    }

    public function suspend(): void
    {
        $this->update([
            'status' => SubcriptionStatus::SUSPENDED->value,
            'suspended_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => SubcriptionStatus::CANCELED->value,
            'suspended_at' => now(),
        ]);
    }

    public function resume(): void
    {
        $this->update([
            'status' => SubcriptionStatus::ACTIVE->value,
            'suspended_at' => null,
        ]);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', SubcriptionStatus::EXPIRED->value)
                    ->orWhere(function ($q) {
                        $q->where('status', SubcriptionStatus::ACTIVE->value)
                          ->whereNotNull('ends_at')
                          ->where('ends_at', '<', now());
                    });
    }

    public function scopeExpiringSoon(Builder $query, int $days = 7): Builder
    {
        return $query->where('status', SubcriptionStatus::ACTIVE->value)
                    ->whereNotNull('ends_at')
                    ->whereBetween('ends_at', [now(), now()->addDays($days)]);
    }

     public function getRemainingDays(): ?int
    {
        if (!$this->ends_at) {
            return null; // Lifetime
        }

        $remaining = now()->diffInDays($this->ends_at, false);
        return max(0, $remaining);
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        if (!$this->ends_at) {
            return false;
        }

        return $this->ends_at->between(now(), now()->addDays($days));
    }

    public function canUpgrade(): bool
    {
        return $this->isActive() || $this->isOnTrial();
    }

    public function canDowngrade(): bool
    {
        return $this->isActive() && !$this->plan->isFree();
    }


}
