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
use App\Services\Workspace\WorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;


class WorkspaceMemberController extends Controller implements HasMiddleware
{
     public function __construct(
        private WorkspaceMemberService $workspaceMemberService,
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

    public function leaveWorkspace(Request $request)
    {

        try {
            DB::beginTransaction();
            $user = auth()->user();
        $workspace = Workspace::find($request->workspaceId);

        $workspaceMember = DB::table('workspace_users')
            ->where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->first();

        // If normal user or admin but not the owner, allow them to leave
        if ($workspaceMember->role_id != 1 || ($workspace->user_id != $user->id)) {

            $workspaceMember->delete();

            DB::commit();

            return ApiResponse::success([], __('You have left the workspace successfully.'));

        }

        // if($workspaceMember->role_id == 1 && $workspace->user_id == $user->id){

        //     return ApiResponse::error(__('Workspace owner cannot leave the workspace. Please transfer ownership or delete the workspace.'), [], HttpStatusCode::FORBIDDEN);

        // }

        DB::commit();

        $this->workspaceService->destroyWorkspace($workspace->id);

        return ApiResponse::success([], __('You have left the workspace successfully.'));

        // $workspaceMemberCount = DB::table('workspace_users')
        //     ->where('workspace_id', $workspace->id)
        //     ->count();

        // $workspaceAdminCount = DB::table('workspace_users')
        //     ->where('workspace_id', $workspace->id)
        //     ->whereNot('user_id', $user->id)
        //     ->where('role_id', 1)
        //     ->count();

        // // If the user is the only admin and not the owner, allow them to leave
        // if($workspaceMemberCount >= 1 && $workspace->user_id != $user->id){

        //     $workspaceMember->delete();

        //     return ApiResponse::success([], __('You have left the workspace successfully.'));

        // }


        //  if($workspaceMemberCount > 1 && $workspaceAdminCount == 1){
        //     $workspace->owner_id = null;
        //     $workspace->save();
        // }

        // if

        // if($workspaceMemberCount <= 1 || ($workspaceMemberCount > 1 && $workspaceAdminCount > 1)){

        //     $this->workspaceService->destroyWorkspace($workspace->id);

        //     return ApiResponse::success([], __('You have left the workspace successfully.'));

        // }


        // if($workspaceMemberCount > 1 && $workspaceAdminCount == 1){
        //     $workspace->owner_id = null;
        //     $workspace->save();
        // }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }


    }
}
