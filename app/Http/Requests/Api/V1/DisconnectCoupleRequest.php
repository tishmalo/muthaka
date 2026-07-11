<?php

namespace App\Http\Requests\Api\V1;

class DisconnectCoupleRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'reason' => 'nullable|string|max:255',
        ];
    }
}

