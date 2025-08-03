<?php

namespace Database\Seeders;

use App\Models\Authorization\Permission;
use App\Models\Authorization\Role;
use App\Services\Authorization\PermissionService;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $permissionService = app(PermissionService::class);

        // Create permissions for different resources
        $permissionService->createPermissionsForResource('workspace', ['change_workspace_name', 'change_workspace_path', 'invite_member', 'remove_member', 'make_member_admin', 'destroy_workspace']);
        //$permissionService->createPermissionsForResource('post', ['create', 'read', 'update', 'delete']);
        //$permissionService->createPermissionsForResource('comment', ['create', 'read', 'update', 'delete']);

        // Create roles
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Full access to workspace',
            'is_system_role' => true,
        ]);

        $memberRole = Role::create([
            'name' => 'member',
            'display_name' => 'Member',
            'description' => 'Basic member access',
            'is_system_role' => true,
        ]);

        // Assign permissions to roles
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

    }

}
