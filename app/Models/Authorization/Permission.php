<?php

namespace App\Models\Authorization;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'display_name', 'description', 'resource', 'action'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    // public static function create(string $resource, string $action, string $displayName = null): self
    // {
    //     $name = "{$resource}.{$action}";
    //     $displayName = $displayName ?: ucfirst($action) . ' ' . ucfirst($resource);

    //     return static::create([
    //         'name' => $name,
    //         'display_name' => $displayName,
    //         'resource' => $resource,
    //         'action' => $action,
    //     ]);
    // }
}
