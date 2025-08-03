<?php

namespace App\Mail\Otp;

use App\Enums\Otp\OtpType;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otpCode,
        public OtpType $type
    ) {}

    public function build()
    {
        return $this->subject("Your OTP Code - {$this->type->label()}")
                    ->view('emails.otp')
                    ->with([
                        'otpCode' => $this->otpCode,
                        'type' => $this->type,
                        'expirationMinutes' => $this->type->getExpirationMinutes(),
                    ]);
    }
}

