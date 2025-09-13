<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\Otp\OtpDeliveryMethod;
use App\Enums\Otp\OtpType;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Exceptions\Otp\OtpException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\CreateForgetPasswordOtpRequest;
use App\Http\Requests\V1\Auth\UpdateForgetPasswordOtpRequest;
use App\Services\Otp\OtpService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller implements HasMiddleware
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


    public function update(Request $request)
    {

        try {

            $user = auth()->user();
            // check current password
            if (!Hash::check($request->currentPassword, $user->password)) {
                return ApiResponse::error(__('auth.password'), [], HttpStatusCode::UNAUTHORIZED);
            }

            // update password
            $user->password = $request->newPassword;
            $user->save();

            $user->currentAccessToken()->delete();

            return ApiResponse::success([], __('passwords.changed'), HttpStatusCode::OK);


        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }


    }




}
