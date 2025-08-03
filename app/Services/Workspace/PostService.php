<?php

namespace App\Services\Workspace;

use App\Models\Workspace\Post;
use App\Models\Workspace\PostAttachment;
use App\Services\Upload\UploadService;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use App\Services\UserSubscription\FeatureAccessService;

class PostService
{

    public function __construct(private FeatureAccessService $featureAccessService, private UploadService $uploadService){}

    public function allPosts(array $data): CursorPaginator
    {
        $searchFilter = $data['filter']['search'] ?? null;
        $membersFilter = $data['filter']['members'] ? explode(',', $data['filter']['members']) : null;
        $workspaceFilter = $data['filter']['workspaces'] ? explode(',', $data['filter']['workspaces']) : null;
        $perPage = $data['pageSize'] ?? 10;

        // First, get workspace IDs that the current user belongs to
        $userPostIds = DB::table('user_posts')
            ->where('user_id', auth()->id())
            ->pluck('post_id');

        // Build the main query
        $query = DB::table('posts')
            ->join('workspaces', 'posts.workspace_id', '=', 'workspaces.id')
            ->join('users', 'posts.created_by', '=', 'users.id')
            ->select('posts.*', 'workspaces.name as workspace_name', 'users.name as user_name', 'users.avatar',             DB::raw('(SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) as comment_count')
)
            ->whereIn('posts.id', $userPostIds)
            ->when($searchFilter, fn ($query) => $query->where('posts.content', 'like', '%' . $searchFilter . '%'))
            ->when($workspaceFilter, fn ($query) => $query->whereIn('posts.id', $workspaceFilter));

        // Apply members filter using a subquery approach
        if ($membersFilter) {
            $query->whereExists(function ($subquery) use ($membersFilter) {
                $subquery->select(DB::raw(1))
                    ->from('user_posts')
                    ->whereColumn('user_posts.post_id', 'posts.id')
                    ->whereIn('user_posts.user_id', $membersFilter);
            });
        }

        $posts = $query->orderBy('posts.created_at', 'desc')
            ->orderBy('posts.updated_at', 'desc')
            ->cursorPaginate($perPage);

        return $posts;
    }

    public function createPost(array $data): Post
    {
        $post = Post::create([
            'content' => $data['content'],
            'workspace_id' => $data['workspaceId'],
            'created_by' => auth()->id(),
        ]);

        $post->members()->attach($post->workspace->members()->pluck('user_id')->toArray());

        foreach ($data['attachments'] as $file) {
            $path = $this->uploadService->uploadFile($file, 'posts/' . $post->id);

            PostAttachment::create([
                'post_id' => $post->id,
                'path' => $path
            ]);
        }

        return $post;
    }

    public function editPost(int $id)
    {
        $post = Post::with('workspace', 'comments', 'comments.creator', 'attachments')->findOrFail($id);

        return $post;
    }

    public function updatePost($id, array $data): Post
    {
        $post = Post::findOrFail($id);

        $post->update([
            'content' => $data['content']
        ]);

        return $post;
    }

    public function destroyPost($id)
    {
        $post = Post::findOrFail($id);

        $post->delete();

    }

}
