<?php

namespace App\Contracts\Otp;

use App\Enums\Otp\OtpType;

interface OtpProviderInterface
{
    public function send(string $identifier, string $otpCode, OtpType $type): bool;
    public function supports(string $identifier): bool;
}
