<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Models\UserSubscription\Plan;
use App\Services\UserSubscription\SubscriptionService;
use Illuminate\Support\Facades\DB;

class AuthRegisterController extends Controller
{
    public function __construct(private AuthService $authService, private SubscriptionService $subscriptionService)
    {
        // Constructor injection for AuthService
    }
    public function __invoke(RegisterRequest $registerRequest)
    {
        try {

            DB::beginTransaction();
            $user = $this->authService->register($registerRequest->validated());

            $plan = Plan::where('key', 'free')->first();

            $this->subscriptionService->subscribe($user, $plan);

            DB::commit();


            return ApiResponse::success([], __('auth.register_success'));

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage());
        }
    }


}
