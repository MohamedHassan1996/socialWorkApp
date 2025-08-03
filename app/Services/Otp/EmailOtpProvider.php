<?php

namespace App\Services\Otp;
use App\Contracts\Otp\OtpProviderInterface;
use App\Enums\Otp\OtpType;
use App\Mail\Otp\OtpMail;
use Illuminate\Support\Facades\Mail;

class EmailOtpProvider implements OtpProviderInterface
{
    public function send(string $identifier, string $otpCode, OtpType $type): bool
    {
        Mail::to($identifier)->send(new OtpMail($otpCode, $type));

        return true;
    }

    public function supports(string $identifier): bool
    {
        return filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
    }
}
