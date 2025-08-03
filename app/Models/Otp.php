<?php

namespace App\Models;

use App\Enums\Otp\OtpDeliveryMethod;
use App\Enums\Otp\OtpType;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
class Otp extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'identifier',
        'otp',
        'type',
        'delivery_method',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => OtpType::class,
            'delivery_method' => OtpDeliveryMethod::class,
            'expires_at' => 'datetime',
        ];
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeForIdentifier(Builder $query, string $identifier): Builder
    {
        return $query->where('identifier', $identifier);
    }

    public function scopeForType(Builder $query, OtpType $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForDeliveryMethod(Builder $query, OtpDeliveryMethod $method): Builder
    {
        return $query->where('delivery_method', $method);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->isExpired();
    }

}
