<?php

namespace App\Http\Requests\Api\V1;

class LoginRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required_without:phone_number|email',
            'phone_number' => 'required_without:email|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ];
    }
}

