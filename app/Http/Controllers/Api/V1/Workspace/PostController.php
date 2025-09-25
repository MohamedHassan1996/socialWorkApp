<?php

namespace App\Http\Controllers\Api\V1\Workspace;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Workspace\Post\CreatePostRequest;
use App\Http\Requests\V1\Workspace\Post\UpdatePostRequest;
use App\Http\Resources\V1\Workspace\Post\AllPostCollection;
use App\Http\Resources\V1\Workspace\Post\PostResource;
use App\Services\Authorization\AuthorizationService;
use App\Services\UserSubscription\FeatureAccessService;
use App\Services\Workspace\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService,
        private FeatureAccessService $featureAccessService,
        private AuthorizationService $authService
    ) {}

    public function index(Request $request)
    {
        $posts = $this->postService->allPosts($request->all());

        return ApiResponse::success(new AllPostCollection($posts));

    }

    public function store(CreatePostRequest $createPostRequest)
    {
        try {
            DB::beginTransaction();

            $data = $createPostRequest->validated();

            $post = $this->postService->createPost($data);


            DB::commit();

            return ApiResponse::success([], __('general.created_successfully'));
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show($post)
    {
        $post = $this->postService->editPost($post);

        return ApiResponse::success(new PostResource($post));
    }

    public function update(UpdatePostRequest $updatePostRequest, $post)
    {
        try {
            DB::beginTransaction();

            $data = $updatePostRequest->validated();

            $post = $this->postService->updatePost($post, $data);

            DB::commit();

            return ApiResponse::success([], __('general.updated_successfully'));
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($post)
    {
        try {
            DB::beginTransaction();

            $this->postService->destroyPost($post);

            DB::commit();

            return ApiResponse::success([], __('general.deleted_successfully'));
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }



}
