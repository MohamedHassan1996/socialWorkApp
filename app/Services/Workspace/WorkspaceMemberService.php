<?php

namespace App\Services\Workspace;

use App\Models\Workspace\Workspace;
use App\Models\Workspace\WorkspaceUser;
use Illuminate\Support\Facades\DB;

class WorkspaceMemberService
{

    public function allWorkspaceMembers(int $workspaceId)
    {
        $workspaceMembers = DB::table('workspace_users')
            ->join('users', 'workspace_users.user_id', '=', 'users.id')
            ->select('workspace_users.*', 'users.name', 'users.email', 'users.avatar')
            ->where('workspace_users.workspace_id', $workspaceId)
            ->get();

        return $workspaceMembers;
    }

    public function addWorkspaceMember(array $data)
    {
        $workspace = Workspace::find($data['workspaceId']);


        foreach ($data['members'] as $key => $member) {

            $roleId  = $member['isAdmin'] == true ? 1 : 2;

            $workspace->members()->attach($member['memberId'], ['role_id' => $roleId]);
        }
    }

    public function changeWorkspaceMemberRole($memberId, array $data)
    {
        $workspaceUser = WorkspaceUser::where('workspace_id', $data['workspaceId'])->where('user_id', $memberId)->first();

        $workspaceUser->role_id = $data['isAdmin'] == true ? 1 : 2;

        $workspaceUser->save();
    }

    public function removeWorkspaceMember(int $id, int $workspaceId)
    {
        $workspace = WorkspaceUser::where('user_id', $id)->where('workspace_id', $workspaceId)->first();
        $workspace->delete();
    }

}
