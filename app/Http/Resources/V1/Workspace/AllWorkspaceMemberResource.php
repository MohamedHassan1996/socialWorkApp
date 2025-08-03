<?php

namespace App\Http\Resources\V1\Workspace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class AllWorkspaceMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'memberId' => $this->user_id ?? $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar??""
        ];
    }
}
