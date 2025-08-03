<?php

namespace App\Services\Authorization;

use App\Models\User;
use App\Models\Workspace\Post;
use App\Models\Workspace\Workspace;

class AuthorizationService
{
    public function __construct(private PermissionService $permissionService) {}

    public function authorize(User $user, string $action, $resource, ?Workspace $workspace = null): bool
    {
        // Handle different resource types
        return match(true) {
            is_string($resource) => $this->authorizeAction($user, $action, $resource, $workspace),
            $resource instanceof Post => $this->authorizePost($user, $action, $resource),
            $resource instanceof Workspace => $this->authorizeWorkspace($user, $action, $resource),
            default => false
        };
    }

    public function authorizeOrFail(User $user, string $action, $resource, ?Workspace $workspace = null): bool
    {
        if (!$this->authorize($user, $action, $resource, $workspace)) {
            return false;
            //abort(403, "Insufficient permissions to {$action}");
        }

        return true;
    }

    private function authorizeAction(User $user, string $action, string $resource, ?Workspace $workspace): bool
    {
        if (!$workspace) {
            return false;
        }

        $permission = "{$resource}.{$action}";
        return $this->permissionService->userCan($user, $permission, $workspace);
    }

    private function authorizePost(User $user, string $action, Post $post): bool
    {
        $workspace = $post->workspace;

        return match($action) {
            'create' => $this->permissionService->userCan($user, 'post.create', $workspace),
            'read' => $this->permissionService->userCan($user, 'post.read', $workspace),
            'update' => $this->canUpdatePost($user, $post),
            'delete' => $this->canDeletePost($user, $post),
            default => false
        };
    }

    private function authorizeWorkspace(User $user, string $action, Workspace $workspace): bool
    {
        return match($action) {
            'read' => $this->canAccessWorkspace($user, $workspace),
            'update' => $this->canUpdateWorkspace($user, $workspace),
            'delete' => $this->canDeleteWorkspace($user, $workspace),
            'invite' => $this->permissionService->userCan($user, 'workspace.invite_user', $workspace),
            'remove_user' => $this->permissionService->userCan($user, 'workspace.remove_user', $workspace),
            default => false
        };
    }

    private function canUpdatePost(User $user, Post $post): bool
    {
        // Users can edit their own posts OR if they have permission
        return $post->created_by === $user->id ||
               $this->permissionService->userCan($user, 'post.update', $post->workspace);
    }

    private function canDeletePost(User $user, Post $post): bool
    {
        // Users can delete their own posts OR if they have permission
        return $post->created_by === $user->id ||
               $this->permissionService->userCan($user, 'post.delete', $post->workspace);
    }

    private function canAccessWorkspace(User $user, Workspace $workspace): bool
    {
        // Owner can always access
        if ($workspace->user_id === $user->id) {
            return true;
        }

        // Check if user is member with read permission
        return $this->permissionService->userCan($user, 'workspace.read', $workspace);
    }

    private function canUpdateWorkspace(User $user, Workspace $workspace): bool
    {
        return $workspace->user_id === $user->id ||
               $this->permissionService->userCan($user, 'workspace.update', $workspace);
    }

    private function canDeleteWorkspace(User $user, Workspace $workspace): bool
    {
        // Only owner can delete workspace
        return $workspace->user_id === $user->id;
    }
}
