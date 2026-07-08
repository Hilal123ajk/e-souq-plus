<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VerifyAdminOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'otp' => ['required', 'digits:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'otp.digits' => 'Please enter the 5-digit verification code.',
        ];
    }
}
