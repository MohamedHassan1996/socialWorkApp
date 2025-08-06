<?php

namespace App\Http\Resources\V1\Workspace\Post;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'postId' => $this->id,
            'content' => $this->content,
            'postPermissions' => [
                [
                    'permissionName' => 'update_post',
                    'permissionAccess' => $this->created_by == auth()->user()->id
                ]
            ],
            'workspace' => [
                'workspaceId' => $this->workspace_id,
                'name' => $this->workspace->name
            ],
            'creator' => [
                'creatorId' => $this->created_by,
                'name' => $this->creator->name,
                'avatar' => $this->creator->avatar
            ],
            'createdAt' => Carbon::parse($this->created_at)->diffForHumans(),
            'totalComments' => $this->total_comments,
            'totalAttachments' => $this->total_attachments,
            'comments' => CommentResource::collection($this->comments),
            'attachments' => PostAttachmentResource::collection($this->attachments)
        ];
    }
}
