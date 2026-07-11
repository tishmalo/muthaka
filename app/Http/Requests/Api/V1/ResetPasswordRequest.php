<?php

namespace App\Http\Requests\Api\V1;

class ResetPasswordRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}

