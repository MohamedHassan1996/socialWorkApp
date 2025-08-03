<?php

namespace App\Http\Controllers\Api\V1\Workspace;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Workspace\UpdateWorkspaceNameRequest;
use App\Http\Requests\V1\Workspace\UpdateWorkspacePathRequest;
use App\Models\Workspace\Workspace;
use App\Services\Authorization\AuthorizationService;
use App\Services\Workspace\WorkspaceService;

class WorkspaceAttributeController extends Controller
{
     public function __construct(
        private WorkspaceService $workspaceService,
        private AuthorizationService $authService
    ) {}

    public function updateName(Workspace $workspace, UpdateWorkspaceNameRequest $updateWorkspaceNameRequest)
    {

        auth()->user()->authorizeOrFail('change_workspace_name', 'workspace', $workspace);

        $this->workspaceService->updateWorkspaceName($workspace->id, $updateWorkspaceNameRequest->validated());

        return ApiResponse::success([], __('general.updated_successfully'));

    }

    public function updatePath(Workspace $workspace, UpdateWorkspacePathRequest $updateWorkspacePathRequest)
    {
        auth()->user()->authorizeOrFail('change_workspace_path', 'workspace', $workspace);

        $this->workspaceService->updateWorkspacePath($workspace->id, $updateWorkspacePathRequest->validated());

        return ApiResponse::success([], __('general.updated_successfully'));

    }

}
