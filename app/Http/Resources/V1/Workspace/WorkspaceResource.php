<?php

namespace App\Http\Resources\V1\Workspace;

use App\Http\Resources\V1\Workspace\Post\AllPostResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class WorkspaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'workspaceId' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'ownerId' => $this->user_id,
            'members' => MemberResource::collection($this->members),
        ];
    }
}
