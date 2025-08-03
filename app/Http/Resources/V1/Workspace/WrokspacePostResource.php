<?php

namespace App\Http\Resources\V1\Workspace;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class WrokspacePostResource extends JsonResource
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
            'workspaceId' => $this->workspace_id,
            'creator' => [
                'memberId' => $this->creator->id,
                'name' => $this->creator->name,
                'avatar' => $this->creator->avatar
            ],
            'createdAt' => Carbon::parse($this->created_at)->diffForHumans(),
            'totalComments' => $this->total_comments,
        ];
    }
}
