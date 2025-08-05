<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\Otp\OtpDeliveryMethod;
use App\Enums\Otp\OtpType;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Exceptions\Otp\OtpException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\CreateForgetPasswordOtpRequest;
use App\Http\Requests\V1\Auth\UpdateForgetPasswordOtpRequest;
use App\Services\Otp\OtpService;
use App\Services\User\UserService;

class ForgetPasswordOtpController extends Controller
{
    public function __construct(private OtpService $otpService, private UserService $userService)
    {
        // Constructor injection for AuthService
    }
    public function store(CreateForgetPasswordOtpRequest $createForgetPasswordOtpRequest)
    {
        try {
            $data = $createForgetPasswordOtpRequest->validated();
            $this->otpService->generate(
            $data['email'],
            OtpType::FORGET_PASSWORD,
            OtpDeliveryMethod::EMAIL
            );


            return ApiResponse::success([], __('general.otp_send_success', ['type' => OtpDeliveryMethod::EMAIL->label()]), HttpStatusCode::CREATED);

        }  catch (OtpException $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateForgetPasswordOtpRequest $updateForgetPasswordOtpRequest)
    {
        $data = $updateForgetPasswordOtpRequest->validated();

        try {
            $this->otpService->verify(
                $data['email'],
                $data['otp'],
                OtpType::FORGET_PASSWORD,
                OtpDeliveryMethod::EMAIL
            );

            $this->userService->changeUserPasswordByEmail($data['email'], $data['password']);

            $this->otpService->delete($data['email'], $data['otp'], OtpType::FORGET_PASSWORD, OtpDeliveryMethod::EMAIL);


            return ApiResponse::success([], __('passwords.changed'), HttpStatusCode::OK);

        }  catch (OtpException $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }


    }




}
