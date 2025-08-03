<?php

namespace App\Services\Authorization;

use App\Models\User;
use App\Models\Authorization\Role;
use App\Models\Workspace\Workspace;
use App\Models\Workspace\WorkspaceUser;
use App\Models\Authorization\Permission;
use App\Repositories\Authorization\PermissionRepository;
use App\Repositories\Authorization\RoleRepository;
use Illuminate\Support\Collection;
class PermissionService
{
    public function __construct(
        private RoleRepository $roleRepository,
        private PermissionRepository $permissionRepository
    ) {}

    public function createRole(string $name, string $displayName, array $permissions = []): Role
    {
        $role = $this->roleRepository->create([
            'name' => $name,
            'display_name' => $displayName,
        ]);

        if (!empty($permissions)) {
            $permissionModels = $this->permissionRepository->getByNames($permissions);
            $role->permissions()->sync($permissionModels->pluck('id'));
        }

        return $role;
    }

    public function assignRoleToUser(User $user, Workspace $workspace, Role $role): WorkspaceUser
    {
        return WorkspaceUser::updateOrCreate(
            ['user_id' => $user->id, 'workspace_id' => $workspace->id],
            ['role_id' => $role->id]
        );
    }

    public function userCan(User $user, string $permission, Workspace $workspace): bool
    {
        // Check if user is workspace owner
        if ($workspace->user_id === $user->id) {
            return true; // Owner has all permissions
        }

        return $user->hasPermissionInWorkspace($permission, $workspace);
    }

    public function createPermissionsForResource(string $resource, array $actions = ['create', 'read', 'update', 'delete']): Collection
    {
        $permissions = collect();

        foreach ($actions as $action) {
            $permission = $this->permissionRepository->createIfNotExists(
                "{$resource}.{$action}",
                [
                    'display_name' => ucfirst($action) . ' ' . ucfirst($resource),
                    'resource' => $resource,
                    'action' => $action,
                ]
            );
            $permissions->push($permission);
        }

        return $permissions;
    }

    public function getRoleByName(string $name): ?Role
    {
        return $this->roleRepository->findByName($name);
    }

    public function getPermissionByName(string $name): ?Permission
    {
        return $this->permissionRepository->findByName($name);
    }

    public function syncRolePermissions(Role $role, array $permissionNames): Role
    {
        $permissions = $this->permissionRepository->getByNames($permissionNames);
        $role->permissions()->sync($permissions->pluck('id'));
        return $role->load('permissions');
    }

    public function addPermissionToRole(Role $role, string $permissionName): bool
    {
        $permission = $this->permissionRepository->findByName($permissionName);
        if (!$permission) {
            return false;
        }

        $role->permissions()->syncWithoutDetaching([$permission->id]);
        return true;
    }

    public function removePermissionFromRole(Role $role, string $permissionName): bool
    {
        $permission = $this->permissionRepository->findByName($permissionName);
        if (!$permission) {
            return false;
        }

        $role->permissions()->detach($permission->id);
        return true;
    }
}
