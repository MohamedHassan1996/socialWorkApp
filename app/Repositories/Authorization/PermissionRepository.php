<?php

namespace App\Repositories\Authorization;

use App\Models\Authorization\Permission;
use App\Models\Authorization\Role;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository
{
    public function findByName(string $name): ?Permission
    {
        return Permission::where('name', $name)->first();
    }

    public function findById(int $id): ?Permission
    {
        return Permission::find($id);
    }

    public function getByNames(array $names): Collection
    {
        return Permission::whereIn('name', $names)->get();
    }

    public function getByIds(array $ids): Collection
    {
        return Permission::whereIn('id', $ids)->get();
    }

    public function getByResource(string $resource): Collection
    {
        return Permission::where('resource', $resource)->get();
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function createIfNotExists(string $name, array $data): Permission
    {
        return Permission::firstOrCreate(['name' => $name], $data);
    }

    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    public function getGroupedByResource(): Collection
    {
        return Permission::all()->groupBy('resource');
    }

    public function updatePermission(Permission $permission, array $data): Permission
    {
        $permission->update($data);
        return $permission->fresh();
    }

    public function deletePermission(Permission $permission): bool
    {
        return $permission->delete();
    }
}
