<?php

namespace App\Http\Controllers\Api\V1\Otp;

use App\Enums\Otp\OtpDeliveryMethod;
use App\Enums\Otp\OtpType;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Exceptions\Otp\OtpException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Otp\VerifyOtpRequest;
use App\Services\Otp\OtpService;

class OtpController extends Controller
{
    public function __construct(private OtpService $otpService)
    {
        // Constructor injection for AuthService
    }
    public function verify(VerifyOtpRequest $verifyOtpRequest)
    {
        try {
            $data = $verifyOtpRequest->validated();
            $this->otpService->verify(
            $data['email'],
            $data['otp'],
            OtpType::from($data['type']),
            OtpDeliveryMethod::from($data['deliveryMethod'])
            );


            return ApiResponse::success([], "");

        }  catch (OtpException $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

}
