<?php

namespace App\Models\Workspace;

use App\Models\Authorization\Role;
use App\Models\User;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Workspace extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'name',
        'path',
        'user_id'
    ];

    protected function path(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Storage::disk('public')->url($value) : "",
        );
    }

     public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // public function members()
    // {
    //     return $this->belongsToMany(User::class, 'workspace_users', 'workspace_id', 'user_id');
    // }

    public function workspaceUsers()
    {
        return $this->hasMany(WorkspaceUser::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'workspace_users')
                    ->withPivot(['role_id'])
                    ->withTimestamps();
    }

    public function membersWithRoles()
    {
        return $this->belongsToMany(User::class, 'workspace_users')
                    ->withPivot('role_id', 'created_at', 'updated_at')
                    ->withTimestamps()
                    ->with(['workspaceRole' => function($query) {
                        // This would require a custom relationship on User model
                    }]);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getUserRole(User $user): ?Role
    {
        $workspaceUser = $this->workspaceUsers()
            ->where('user_id', $user->id)
            ->with('role')
            ->first();

        return $workspaceUser?->role;
    }

}
