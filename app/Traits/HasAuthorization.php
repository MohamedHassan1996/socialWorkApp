<?php

namespace App\Traits;

use App\Models\Workspace\Workspace;
use App\Services\Authorization\AuthorizationService;

trait HasAuthorization
{
    public function authorize(string $action, $resource, ?Workspace $workspace = null): bool
    {
        return app(AuthorizationService::class)->authorize($this, $action, $resource, $workspace);
    }

    public function authorizeOrFail(string $action, $resource, ?Workspace $workspace = null): bool
    {
        return app(AuthorizationService::class)->authorizeOrFail($this, $action, $resource, $workspace);
    }

    public function canCreate(string $resource, Workspace $workspace): bool
    {
        return $this->authorize('create', $resource, $workspace);
    }

    public function canUpdate($resource): bool
    {
        return $this->authorize('update', $resource);
    }

    public function canDelete($resource): bool
    {
        return $this->authorize('delete', $resource);
    }

    public function canRead($resource, ?Workspace $workspace = null): bool
    {
        return $this->authorize('read', $resource, $workspace);
    }
}
