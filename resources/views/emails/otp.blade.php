<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OTP Verification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h1 style="color: #333; text-align: center; margin-bottom: 30px;">{{ $type->label() }}</h1>

        <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
            Hello,
        </p>

        <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
            Your verification code is:
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <div style="display: inline-block; background-color: #f8f9fa; border: 2px dashed #dee2e6; padding: 20px 30px; border-radius: 8px;">
                <h2 style="color: #007bff; font-size: 32px; margin: 0; letter-spacing: 5px; font-weight: bold;">
                    {{ $otpCode }}
                </h2>
            </div>
        </div>

        <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
            This code will expire in <strong>{{ $expirationMinutes }} minutes</strong>.
        </p>

        <p style="color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 0;">
            If you didn't request this code, please ignore this email.
        </p>

        <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">

        <p style="color: #999; font-size: 12px; text-align: center; margin: 0;">
            This is an automated message, please do not reply.
        </p>
    </div>
</body>
</html>
