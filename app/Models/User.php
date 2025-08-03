<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\SubcriptionPlan\SubcriptionStatus;
use App\Enums\User\UserStatus;
use App\Enums\User\UserType;
use App\Models\Authorization\Role;
use App\Models\Notification\Notification;
use App\Models\UserSubscription\FeatureUsage;
use App\Models\UserSubscription\Plan;
use App\Models\UserSubscription\Subscription;
use App\Models\Workspace\Workspace;
use App\Models\Workspace\WorkspaceUser;
use App\Traits\HasAuthorization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasAuthorization;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'type',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => UserStatus::class,
            'type' => UserType::class
        ];
    }

    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Storage::disk('public')->url($value) : "",
        );
    }

    public function currentSubscription()
    {
        return $this->hasMany(Subscription::class, 'user_id')
                    ->where('status', SubcriptionStatus::ACTIVE->value)
                    ->latest()
                    ->first();
    }
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function featureUsages(): HasMany
    {
        return $this->hasMany(FeatureUsage::class);
    }

    public function getCurrentPlan(): ?Plan
    {
        return $this->currentSubscription()?->plan;
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', SubcriptionStatus::ACTIVE->value);
    }

    // public function workspaces(): BelongsToMany
    // {
    //     return $this->belongsToMany(Workspace::class, 'workspace_users', 'user_id', 'workspace_id');
    // }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'workspace_users')
                    ->withPivot('role_id', 'created_at', 'updated_at')
                    ->withTimestamps();
    }
    public function isActive(): bool
    {
        return $this->is_active === UserStatus::ACTIVE;
    }



    public function workspaceUsers()
    {
        return $this->hasMany(WorkspaceUser::class);
    }

    public function hasPermissionInWorkspace(string $permission, Workspace $workspace): bool
    {
        $role = $workspace->getUserRole($this);
        return $role?->hasPermission($permission) ?? false;
    }

    public function getRoleInWorkspace(Workspace $workspace): ?Role
    {
        return $workspace->getUserRole($this);
    }

    public function hasActivePlan(): bool
    {
        return $this->subscription !== null && $this->subscription->isActive();
    }


    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function unreadCount()
    {
        return $this->unreadNotifications()->count();
    }
}
