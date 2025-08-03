<?php

namespace App\Traits;

use App\Models\Workspace\Workspace;
use App\Services\User\PermissionService;

trait HasWorkspacePermissions
{
    public function can(string $permission, Workspace $workspace): bool
    {
        return app(PermissionService::class)->userCan($this, $permission, $workspace);
    }

    public function cannot(string $permission, Workspace $workspace): bool
    {
        return !$this->can($permission, $workspace);
    }
}
