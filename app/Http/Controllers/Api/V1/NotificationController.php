<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Notification\AllNotificationCollection;
use App\Models\Notification\Notification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class NotificationController extends Controller implements HasMiddleware
{

    public function __construct(
    ) {}
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')->cursorPaginate($request->get('pageSize', 10));

        return ApiResponse::success(new AllNotificationCollection($notifications));

    }

    public function markRead(Request $request)
    {
        $notifications = Notification::whereIn('id', $request->notifacationIds)->get();

        Notification::whereIn('id', $notifications->pluck('id'))
        ->update(['read_at' => now()]);


        return ApiResponse::success([], __('general.updated_successfully'));


    }

    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return ApiResponse::success(['unreadCount' => $count]);
    }
}
