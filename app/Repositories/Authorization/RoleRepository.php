<?php

namespace App\Repositories\Authorization;

use App\Models\Authorization\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    public function findByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }

    public function findById(int $id): ?Role
    {
        return Role::find($id);
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function getSystemRoles(): Collection
    {
        return Role::where('is_system_role', true)->get();
    }

    public function getAllRoles(): Collection
    {
        return Role::all();
    }

    public function getRolesWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function updateRole(Role $role, array $data): Role
    {
        $role->update($data);
        return $role->fresh();
    }

    public function deleteRole(Role $role): bool
    {
        // Don't delete system roles
        if ($role->is_system_role) {
            throw new \Exception('Cannot delete system role');
        }

        return $role->delete();
    }
}
