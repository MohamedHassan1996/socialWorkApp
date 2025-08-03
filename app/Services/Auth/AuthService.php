<?php

namespace App\Services\Auth;

use App\Enums\User\UserStatus;
use App\Enums\User\UserType;
use App\Exceptions\Auth\InactiveAccountException;
use App\Exceptions\Auth\InvalidCredentialsException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => UserStatus::ACTIVE->value,
            'type' => UserType::CLIENT->value,
            'email_verified_at' => now(), // Assuming email verification is not required for registration
        ]);


        return $user;

    }
    public function login(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw new InvalidCredentialsException();
        }


        if (!$user->isActive()) {
            throw new InactiveAccountException();
        }



        // Generate a new token (DO NOT return it directly)
        $token = $user->createToken('auth_token')->plainTextToken;

        if($currentSubcription = $user->currentSubscription()) {
            $activeSubscription = $currentSubcription->isActive();
            $subscriptionEndAt = $activeSubscription ? $currentSubcription->ends_at : null;
        }

        return [
            'profile' => $user,
            'hasActiveSubscription' => $activeSubscription ,//$activeSubscription,
            'subcriptionEndAt' => $subscriptionEndAt,
            'tokenDetails' => [
                'token' => $token,
                'expiresIn' => null
            ],
        ];

    }

    public function logout()
    {
        $user = auth()->user();

        if ($user) {
            //$user->tokens()->delete(); // Revoke all tokens
            $user->currentAccessToken()->delete();
        }
    }
}
