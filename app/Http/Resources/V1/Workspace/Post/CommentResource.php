<?php

namespace App\Http\Resources\V1\Workspace\Post;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'commentId' => $this->id,
            'content' => $this->content,
            'creator' => [
                'creatorId' => $this->created_by,
                'name' => $this->creator->name,
                'avatar' => $this->creator->avatar
            ],
            'createdAt' => Carbon::parse($this->created_at)->diffForHumans(),
        ];
    }
}
