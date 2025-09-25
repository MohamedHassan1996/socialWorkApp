<?php

namespace App\Http\Resources\V1\Notification;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class AllNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
           'notificationId' => $this->id,
           'message' => $this->message,
           'isRead' => $this->read_at?1:0,
           'data' => $this->data,
           'createdAt' => Carbon::parse($this->created_at)->diffForHumans(),
       ];
    }
}
