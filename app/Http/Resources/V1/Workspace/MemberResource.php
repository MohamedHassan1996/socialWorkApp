<?php

namespace App\Http\Resources\V1\Workspace;

use App\Models\Authorization\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get role name if Role model exists
        $role = null;
        if ($this->pivot && $this->pivot->role_id) {
            $role = $this->pivot->role_id;
        }

        return [
            'memberId' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar ?? "",
            'isAdmin' => $role && $role == 1
        ];
    }
}
