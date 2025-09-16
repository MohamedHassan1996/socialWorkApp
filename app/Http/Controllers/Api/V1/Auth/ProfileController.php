<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\UserProfileResource;
use App\Services\Otp\OtpService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller implements HasMiddleware
{
    public function __construct(private OtpService $otpService, private UserService $userService)
    {
        new Middleware('auth:api');
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    public function show(Request $request)
    {
        try {
            $user = auth()->user();

            return ApiResponse::success(new UserProfileResource($user), __('test'), HttpStatusCode::OK);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }


    public function updateName(Request $request)
    {

        try {

            $user = auth()->user();

            $user->name = $request->name;
            $user->save();

            return ApiResponse::success([], __('test'), HttpStatusCode::OK);


        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }


    }

    public function updateAvatar(Request $request)
    {
        try {

            $user = auth()->user();

            if($user->avatar){
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = Storage::disk('public')->put('avatars', $request->file('avatar'));

            $user->save();


            return ApiResponse::success([], __('test'), HttpStatusCode::OK);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }

    }




}
