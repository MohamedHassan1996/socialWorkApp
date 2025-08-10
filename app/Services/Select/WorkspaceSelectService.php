<?php

namespace App\Services\Select;

use Illuminate\Support\Facades\DB;

class WorkspaceSelectService
{
    public function getAllRelatedWorkspaces()
    {
        $auth = auth()->user();

        return DB::table('workspaces')
            ->join('workspace_users', 'workspaces.id', '=', 'workspace_users.workspace_id')
            ->where('workspace_users.user_id', $auth->id)
            ->select('workspaces.id as value', 'workspaces.name as label')
            ->get();
    }
}
