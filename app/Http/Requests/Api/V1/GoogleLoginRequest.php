<?php

namespace App\Http\Requests\Api\V1;

class GoogleLoginRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'id_token' => 'required|string',
            'device_name' => 'nullable|string|max:255',
            // Optional: lets Google users set a real phone number at signup
            // (phone verification is not enforced — no SMS resources yet).
            'phone_number' => 'nullable|string|unique:users,phone_number',
        ];
    }
}
