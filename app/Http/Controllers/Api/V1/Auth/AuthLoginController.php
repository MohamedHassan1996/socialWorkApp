<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Resources\V1\User\LoggedInUserResource;
use App\Services\Auth\AuthService;
use App\Exceptions\Auth\InactiveAccountException;
use App\Exceptions\Auth\InvalidCredentialsException;


class AuthLoginController extends Controller
{
    public function __construct(private AuthService $authService)
    {
        // Constructor injection for AuthService
    }

    public function __invoke(LoginRequest $loginRequest)
    {
        try {
            $auth = $this->authService->login($loginRequest->validated());

            return ApiResponse::success([
                'profile' => new LoggedInUserResource($auth['profile']),
                'hasActiveSubscription' => $auth['hasActiveSubscription'],
                'subcriptionEndAt' => $auth['subcriptionEndAt'],
                'tokenDetails' => $auth['tokenDetails']
            ]);

        } catch (InvalidCredentialsException $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::UNAUTHORIZED);
        } catch (InactiveAccountException $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::UNAUTHORIZED);
        } catch (\Exception $e) {
            return ApiResponse::error();
        }
    }


}
