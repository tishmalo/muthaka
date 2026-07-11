<?php

namespace App\Http\Requests\Api\V1;

class CoupleInviteRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'phone_number' => 'required|string|exists:users,phone_number',
        ];
    }
}

