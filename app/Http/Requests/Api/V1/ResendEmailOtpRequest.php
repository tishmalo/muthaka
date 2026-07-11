<?php

namespace App\Http\Requests\Api\V1;

class ResendEmailOtpRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }
}

