<?php

namespace App\Http\Controllers\Api\V1\Workspace;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Workspace\Post\CreatePostCommentRequest;
use App\Http\Requests\V1\Workspace\Post\CreatePostRequest;
use App\Http\Requests\V1\Workspace\Post\UpdatePostCommentRequest;
use App\Http\Requests\V1\Workspace\Post\updatePostRequest;
use App\Http\Resources\V1\Workspace\Post\AllPostCollection;
use App\Http\Resources\V1\Workspace\Post\AllPostCommentCollection;
use App\Http\Resources\V1\Workspace\Post\CommentResource;
use App\Http\Resources\V1\Workspace\Post\PostResource;
use App\Services\Authorization\AuthorizationService;
use App\Services\UserSubscription\FeatureAccessService;
use App\Services\Workspace\PostCommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostCommentController extends Controller
{
    public function __construct(
        private PostCommentService $postCommentService,
        private FeatureAccessService $featureAccessService,
        private AuthorizationService $authService
    ) {}

    public function index(Request $request)
    {
        $comments = $this->postCommentService->allPostComments($request->all());

        return ApiResponse::success(new AllPostCommentCollection($comments));

    }

    public function show($postComment)
    {
        $postComment = $this->postCommentService->editPostComment($postComment);

        return ApiResponse::success(new CommentResource($postComment));
    }

    public function store(CreatePostCommentRequest $createPostCommentRequest)
    {
        try {
            DB::beginTransaction();

            $data = $createPostCommentRequest->validated();

            $this->postCommentService->createPostComment($data);

            DB::commit();

            return ApiResponse::success([], __('general.created_successfully'));
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    // public function show($post)
    // {
    //     $post = $this->postService->editPost($post);

    //     return ApiResponse::success(new PostResource($post));
    // }

    public function update(UpdatePostCommentRequest $updatePostCommentRequest, $postComment)
    {
        try {
            DB::beginTransaction();

            $data = $updatePostCommentRequest->validated();

            $post = $this->postCommentService->updatePostComment($postComment, $data);

            DB::commit();

            return ApiResponse::success([], __('general.updated_successfully'));
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($postComment)
    {
        try {
            DB::beginTransaction();

            $this->postCommentService->destroyPostComment($postComment);

            DB::commit();

            return ApiResponse::success([], __('general.deleted_successfully'));
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }



}
