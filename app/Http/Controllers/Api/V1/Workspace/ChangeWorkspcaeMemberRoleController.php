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


class ChangeWorkspcaeMemberRoleController extends Controller implements HasMiddleware
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


    public function changeRole($workspaceMember, Request $request)
    {
        try {

            $user = auth()->user();

            $workspace = Workspace::find($request->workspaceId);

            $user->authorizeOrFail('make_member_admin', 'workspace', $workspace);

            $this->workspaceMemberService->changeWorkspaceMemberRole($workspaceMember, $request->all());


            return ApiResponse::success([], __('general.updated_successfully'));

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }


}
