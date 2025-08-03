<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class AuthLogoutController extends Controller implements HasMiddleware
{
    public function __construct(private AuthService $authService)
    {
        // Constructor injection for AuthService
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }
    public function __invoke()
    {
        try {
            $this->authService->logout();

            return ApiResponse::success([], __('auth.logged_out'));

        } catch (\Exception $e) {
            return ApiResponse::error();
        }
    }


}
