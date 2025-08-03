<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class LoggedInUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            //'userId' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'email' => $this->email??""
        ];
    }
}
