<?php

namespace App\Services\User;

use App\Models\User;

class UserService
{
    public function changeUserPasswordByEmail(string $email, string $password): void
    {
        $user = $this->getUserByEmail($email);

        $user->password = $password;
        $user->save();
    }

    public function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
