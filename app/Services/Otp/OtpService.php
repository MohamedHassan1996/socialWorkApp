<?php

namespace App\Services\Otp;

use App\Contracts\Otp\OtpProviderInterface;
use App\Enums\Otp\OtpDeliveryMethod;
use App\Enums\Otp\OtpType;
use App\Exceptions\Otp\OtpException;
use App\Models\Otp;

class OtpService
{
    private const RATE_LIMIT_MINUTES = 0; // Minimum time between OTP requests

    public function generate(string $identifier, OtpType $type, OtpDeliveryMethod $deliveryMethod): string
    {
        // Check rate limiting
        $this->checkRateLimit($identifier, $type, $deliveryMethod);

        // Invalidate existing OTPs for this identifier, type, and delivery method
        $this->invalidateExistingOtps($identifier, $type, $deliveryMethod);

        // Generate OTP
        $otpCode = $this->generateOtpCode();

        // Create OTP record
        $otp = Otp::create([
            'identifier' => $identifier,
            'otp' => $otpCode,
            'type' => $type,
            'delivery_method' => $deliveryMethod,
            'expires_at' => now()->addMinutes($type->getExpirationMinutes()),
        ]);

        // Send OTP via selected delivery method
        $this->sendOtp($identifier, $otpCode, $type, $deliveryMethod);

        // Clean up expired OTPs (optional, can be moved to scheduled job)
        $this->cleanupExpiredOtps();

        return $otpCode;
    }

    public function verify(
        string $identifier,
        string $otpCode,
        OtpType $type,
        ?OtpDeliveryMethod $deliveryMethod = null
    ): bool {
        $query = Otp::forIdentifier($identifier)
                    ->forType($type)
                    ->where('otp', $otpCode)
                    ->valid();

        // If delivery method is specified, filter by it
        if ($deliveryMethod) {
            $query->forDeliveryMethod($deliveryMethod);
        }

        $otp = $query->first();

        if (!$otp) {
            throw new OtpException('Invalid OTP code.');
        }

        if ($otp->isExpired()) {
            throw new OtpException('OTP has expired.');
        }

        // Delete the OTP after successful verification to prevent reuse
        //$otp->delete();

        return true;
    }

    // public function resend(string $identifier, OtpType $type, OtpDeliveryMethod $deliveryMethod): string
    // {
    //     return $this->generate($identifier, $type, $deliveryMethod);
    // }

    private function checkRateLimit(
        string $identifier,
        OtpType $type,
        OtpDeliveryMethod $deliveryMethod
    ): void {
        $recentOtp = Otp::forIdentifier($identifier)
                        ->forType($type)
                        ->forDeliveryMethod($deliveryMethod)
                        ->where('created_at', '>', now()->subMinutes(self::RATE_LIMIT_MINUTES))
                        ->first();

        if ($recentOtp) {
            $waitTime = self::RATE_LIMIT_MINUTES - now()->diffInMinutes($recentOtp->created_at);
            throw new OtpException("Please wait {$waitTime} minutes before requesting another OTP.");
        }
    }

    private function invalidateExistingOtps(
        string $identifier,
        OtpType $type,
        OtpDeliveryMethod $deliveryMethod
    ): void {
        Otp::forIdentifier($identifier)
           ->forType($type)
           ->forDeliveryMethod($deliveryMethod)
           ->valid()
           ->delete();
    }

    private function generateOtpCode(): string
    {
        return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function sendOtp(
        string $identifier,
        string $otpCode,
        OtpType $type,
        OtpDeliveryMethod $deliveryMethod
    ): void {
        $providerClass = $deliveryMethod->getProviderClass();
        $provider = app($providerClass);

        if (!$provider instanceof OtpProviderInterface) {
            throw new OtpException('Invalid OTP provider configuration.');
        }

        if (!$provider->supports($identifier)) {
            throw new OtpException("The identifier '{$identifier}' is not supported by {$deliveryMethod->label()}.");
        }

        $success = $provider->send($identifier, $otpCode, $type);

        if (!$success) {
            throw new OtpException("Failed to send OTP via {$deliveryMethod->label()}.");
        }
    }

    private function cleanupExpiredOtps(): void
    {
        Otp::expired()->delete();
    }

    // Helper method to auto-detect delivery method based on identifier
    // public function detectDeliveryMethod(string $identifier): OtpDeliveryMethod
    // {
    //     if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
    //         return OtpDeliveryMethod::EMAIL;
    //     }

    //     if (preg_match('/^\+?[1-9]\d{1,14}$/', $identifier)) {
    //         return OtpDeliveryMethod::SMS; // Default to SMS for phone numbers
    //     }

    //     throw new OtpException('Could not detect delivery method for identifier: ' . $identifier);
    // }

    public function delete(string $identifier,  string $otpCode, OtpType $type, OtpDeliveryMethod $deliveryMethod): void
    {
        Otp::forIdentifier($identifier)
           ->forType($type)
           ->forDeliveryMethod($deliveryMethod)
           ->where('otp', $otpCode)
           ->delete();
    }
}
