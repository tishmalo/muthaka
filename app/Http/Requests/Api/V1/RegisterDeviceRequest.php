<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;

class RegisterDeviceRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required|string|max:2048',
            'platform' => ['required', 'string', Rule::in(['android', 'ios'])],
            'device_name' => 'nullable|string|max:255',
            'device_id' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:50',
            'os_version' => 'nullable|string|max:50',
        ];
    }
}

