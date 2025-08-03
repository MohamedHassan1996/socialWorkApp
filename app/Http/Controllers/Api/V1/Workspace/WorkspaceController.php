<?php

namespace App\Http\Controllers\Api\V1\Workspace;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Workspace\CreateWorkspaceRequest;
use App\Http\Resources\V1\Workspace\AllWorkspcaeCollection;
use App\Http\Resources\V1\Workspace\WorkspaceResource;
use App\Models\Workspace\Workspace;
use App\Services\Authorization\AuthorizationService;
use App\Services\UserSubscription\FeatureAccessService;
use App\Services\Workspace\WorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class WorkspaceController extends Controller implements HasMiddleware
{
    public function __construct(
        private WorkspaceService $workspaceService,
        private FeatureAccessService $featureAccessService,
        private AuthorizationService $authService
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }
    public function index(Request $request)
    {
        $workspaces = $this->workspaceService->allWorkspaces($request->all());

        return ApiResponse::success(new AllWorkspcaeCollection($workspaces));

    }
    public function store(CreateWorkspaceRequest $createWorkspaceRequest)
    {
        try {

            $user = auth()->user();

            // Check if user can create workspace
            if (!$this->featureAccessService->canUseFeature($user, 'workspace_limit')) {
                return ApiResponse::error(__('subscription_plan.feature_limit'), [], HttpStatusCode::FORBIDDEN);
            }


            $workspace = $this->workspaceService->createWorkspace($createWorkspaceRequest->validated());


            $this->featureAccessService->recordUsage($user, 'workspace_limit');


            return ApiResponse::success([
                'remainingWorkspaces' => $this->featureAccessService->getRemainingUsage($user, 'workspace_limit')
            ], __('general.created_successfully'));

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function show($workspace)
    {
        $workspace = $this->workspaceService->editWorkspace($workspace);

        return ApiResponse::success(new WorkspaceResource($workspace));

    }

    public function destroy(Workspace $workspace)
    {
        auth()->user()->authorizeOrFail('destroy_workspace', 'workspace', $workspace);
        $this->workspaceService->destroyWorkspace($workspace->id);
        return ApiResponse::success([], __('general.deleted_successfully'));
    }

}
