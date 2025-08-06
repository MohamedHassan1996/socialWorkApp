<?php

namespace App\Http\Resources\V1\Workspace\Post;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class AllPostResource extends JsonResource
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
            'createdAt' => Carbon::parse($this->created_at)->diffForHumans(), // Fully localized
            'postPermissions' => [
                [
                    'permissionName' => 'update_post',
                    'permissionAccess' => $this->created_by == auth()->user()->id
                ]
            ],
            'totalComments' => $this->comment_count,
            'workspace' => [
                'name' => $this->workspace_name
            ],
            'creator' => [
                'name' => $this->user_name,
                'avatar' => $this->avatar??"",
            ]
       ];
    }
}
