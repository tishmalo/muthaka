<?php

namespace App\Http\Requests\Api\V1;

class CoupleInviteRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }
}

