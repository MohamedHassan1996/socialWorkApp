<?php

namespace App\Http\Middleware;

use App\Services\User\PermissionService;
use Closure;
use Illuminate\Http\Request;

class CheckWorkspacePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();
        $workspace = $request->route('workspace');

        if (!$workspace) {
            abort(404, 'Workspace not found');
        }

        $permissionService = app(PermissionService::class);

        if (!$permissionService->userCan($user, $permission, $workspace)) {
            abort(403, 'Insufficient permissions');
        }

        return $next($request);
    }
}
