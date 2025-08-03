<?php

namespace App\Http\Resources\V1\Workspace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class AllWorkspaceResource extends JsonResource
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
           'members' => AllWorkspaceMemberResource::collection($this->members),
           'totalMembers' => $this->totalMembers
       ];
    }
}
