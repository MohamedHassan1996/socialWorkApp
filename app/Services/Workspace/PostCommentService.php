<?php

namespace App\Services\Workspace;

use App\Models\Workspace\Comment;
use App\Models\Workspace\Post;
use App\Services\Upload\UploadService;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use App\Services\UserSubscription\FeatureAccessService;

class PostCommentService
{

    public function __construct(private FeatureAccessService $featureAccessService, private UploadService $uploadService){}

    public function allPostComments(array $data): CursorPaginator
    {
        $perPage = $data['pageSize'] ?? 10;

        // Build the main query
        $query = DB::table('comments')
            ->join('users', 'comments.created_by', '=', 'users.id')
            ->select('comments.*', 'users.name as user_name', 'users.avatar',
            DB::raw("'(SELECT COUNT(*) FROM comments WHERE comments.post_id = ".$data['postId'].") as comment_count'"))
            ->where('comments.id', $data['postId']);

        $comments = $query->orderBy('comments.created_at', 'desc')
            ->orderBy('comments.updated_at', 'desc')
            ->cursorPaginate($perPage);

        return $comments;
    }

    public function createPostComment(array $data): Comment
    {
        $postComment = Comment::create([
            'content' => $data['content'],
            'post_id' => $data['postId'],
        ]);



        return $postComment;
    }

    public function editPostComment(int $id)
    {
        $postComment = Comment::with('creator')->findOrFail($id);

        return $postComment;

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
