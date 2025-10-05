<?php

namespace App\Services\Workspace;

use App\Models\Authorization\Role;
use App\Models\Workspace\Workspace;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use App\Services\UserSubscription\FeatureAccessService;

class WorkspaceService
{

    public function __construct(private FeatureAccessService $featureAccessService){}

    public function allWorkspaces(array $data): CursorPaginator
    {
        $nameFilter = $data['filter']['search'] ?? null;
        $membersFilter = isset($data['filter']['members'])? explode(',', $data['filter']['members']) : null;
        $perPage = $data['pageSize'] ?? 10;

        // First, get workspace IDs that the current user belongs to
        $userWorkspaceIds = DB::table('workspace_users')
            ->where('user_id', auth()->id())
            ->pluck('workspace_id');

        // Build the main query
        $query = DB::table('workspaces')
            ->select('workspaces.*')
            ->whereIn('workspaces.id', $userWorkspaceIds)
            ->when($nameFilter, fn ($query) => $query->where('workspaces.name', 'like', '%' . $nameFilter . '%'));

        // Apply members filter using a subquery approach
        if ($membersFilter) {
            $query->whereExists(function ($subquery) use ($membersFilter) {
                $subquery->select(DB::raw(1))
                    ->from('workspace_users')
                    ->whereColumn('workspace_users.workspace_id', 'workspaces.id')
                    ->whereIn('workspace_users.user_id', $membersFilter);
            });
        }

        $workspaces = $query->orderBy('workspaces.created_at', 'desc')
            ->cursorPaginate($perPage);

        // Map each workspace to include users and totalUsers
        $transformed = $workspaces->through(function ($workspace) {
            // Get up to 4 users
            $users = DB::table('users')
                ->join('workspace_users', 'users.id', '=', 'workspace_users.user_id')
                ->where('workspace_users.workspace_id', $workspace->id)
                ->select('users.id', 'users.name', 'users.email', 'users.avatar')
                ->limit(4)
                ->get();

            // Count total users
            $totalUsers = DB::table('workspace_users')
                ->where('workspace_id', $workspace->id)
                ->count();

            return (object) [
                ...((array) $workspace),
                'members' => $users,
                'totalMembers' => $totalUsers,
            ];
        });

        return $transformed;
    }

    public function createWorkspace(array $data): Workspace
    {
        $workspace = Workspace::create([
            'name' => $data['name'],
            'path' => $data['path'],
            'user_id' => auth()->id(),
        ]);

        // Include the creator (auth user) in the members list if not already
        $memberIds = $data['members'] ?? [];

        dd($memberIds);

        // Get authenticated user
        $authUser = auth()->user();

        // Prevent duplicates
        $memberIds = array_diff($memberIds, [$authUser->id]);

        // Get role IDs from your roles table (assuming you have a Role model)
        $adminRoleId = Role::where('name', 'admin')->value('id');
        $memberRoleId = Role::where('name', 'member')->value('id');

        // Attach authenticated user as admin
        $workspace->members()->attach($authUser->id, ['role_id' => $adminRoleId]);

        $this->featureAccessService->recordUsage($authUser, 'workspace_member_limit', 'workspace', $workspace->id);

        // Attach each member with member role
        foreach ($memberIds as $memberId) {
            $workspace->members()->attach($memberId['memberId'], ['role_id' => $memberId['isAdmin'] ? $adminRoleId : $memberRoleId]);

            $this->featureAccessService->recordUsage($authUser, 'workspace_member_limit', 'workspace', $workspace->id);

        }

        return $workspace;
    }

    public function editWorkspace(int $id)
    {
        $workspace = Workspace::with([
            'workspaceUsers.user',
            'workspaceUsers.role',
            // 'posts' => function ($query) {
            //     $query->whereHas('members', function ($q) {
            //         $q->where('user_id', auth()->id());
            //     });
            // }
        ])->find($id);

        return $workspace;
    }

    public function destroyWorkspace(int $id)
    {
        $workspace = Workspace::find($id);
        $workspace->workspaceUsers()->delete();

        foreach($workspace->posts()->get() as $post){
            $post->delete();
        }

       // $workspace->posts()->delete(); // If you want to delete related posts as well
        $workspace->delete();
    }

    public function updateWorkspaceName(int $id, array $data)
    {
        $workspace = Workspace::find($id);

        $workspace->name = $data['name'];

        $workspace->save();
    }

    public function updateWorkspacePath(int $id, array $data)
    {
        $workspace = Workspace::find($id);

        $workspace->path = $data['path'];

        $workspace->save();
    }

}
