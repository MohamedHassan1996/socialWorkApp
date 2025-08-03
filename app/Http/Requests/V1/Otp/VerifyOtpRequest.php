<?php

namespace App\Http\Requests\V1\Otp;

use App\Enums\Otp\OtpDeliveryMethod;
use App\Enums\Otp\OtpType;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\ApiResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class VerifyOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')//->whereNull('deleted_at'),
            ],
            'otp' => [
                'required',
                'string',
                'size:6',
                Rule::exists('otps', 'otp')->where('type', $this->type)->where('identifier', $this->email)->where('delivery_method', $this->deliveryMethod),
            ],
            'type' => ['required', new Enum(OtpType::class)],
            'deliveryMethod' => ['required', new Enum(OtpDeliveryMethod::class)],
        ];
    }

    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }

    public function messages(): array
    {
        return [
            'email.exists' => __('validation.custom.email.not_exists'),
            'otp.exists' => __('general.otp_not_valid'),
        ];
    }

}
