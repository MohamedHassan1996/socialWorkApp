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
            'workspaceName' => $this->workspace_name,
            'memberName' => $this->user_name,
            'memberAvatar' => $this->avatar??"",
            'isEditable' => $this->created_by == auth()->user()->id,
            'totalComments' => $this->comment_count
       ];
    }
}
