<?php

namespace App\Http\Resources\V1\Workspace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AllMemberResource extends JsonResource
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
            'avatar' => $this->avatar? Storage::disk('public')->url($this->avatar) : "",
            'isAdmin' => $this->role_id == 1
        ];
    }
}
