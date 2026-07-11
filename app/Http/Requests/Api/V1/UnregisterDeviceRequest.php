<?php

namespace App\Http\Requests\Api\V1;

class UnregisterDeviceRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required_without:device_id|string|max:2048',
            'device_id' => 'required_without:token|string|max:255',
        ];
    }
}

