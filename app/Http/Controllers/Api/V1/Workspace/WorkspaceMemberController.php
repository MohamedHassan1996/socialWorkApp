<?php

namespace App\Http\Controllers\Api\V1\Workspace;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Workspace\AllMemberResource;
use App\Models\Workspace\Workspace;
use App\Services\Authorization\AuthorizationService;
use App\Services\UserSubscription\FeatureAccessService;
use App\Services\Workspace\WorkspaceMemberService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class WorkspaceMemberController extends Controller implements HasMiddleware
{
     public function __construct(
        private WorkspaceMemberService $workspaceMemberService,
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
        $workspaceMembers = $this->workspaceMemberService->allWorkspaceMembers($request->workspaceId);

        return ApiResponse::success(AllMemberResource::collection($workspaceMembers));

    }
    public function addMember(Request $request)
    {
        try {

            $user = auth()->user();

            $workspace = Workspace::find($request->workspaceId);

            $user->authorizeOrFail('add_workspace_member', 'workspace', $workspace);

            $workspaceCreator = $workspace->owner->id === $user->id ? $user : $workspace->owner;

            // Check if user can add members
            if (!$this->featureAccessService->canUseFeature($workspaceCreator, 'workspace_member_limit', 'workspace', $workspace->id)) {
                return ApiResponse::error(__('subscription_plan.feature_limit'), [], HttpStatusCode::FORBIDDEN);
            }

            $this->workspaceMemberService->addWorkspaceMember($request->all());

            $this->featureAccessService->recordUsage($workspaceCreator, 'workspace_member_limit', 'workspace', $workspace->id);


            return ApiResponse::success([
                'remainingWorkspaces' => $this->featureAccessService->getRemainingUsage($workspaceCreator, 'workspace_member_limit')
            ], __('general.created_successfully'));

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
    public function destroy(int $workspaceMember, Request $request)
    {
        $workspace = Workspace::find($request->workspaceId);
        $this->workspaceMemberService->removeWorkspaceMember($workspaceMember, $request->workspaceId);
        return ApiResponse::success([], __('general.deleted_successfully'));
    }

}
