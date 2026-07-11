<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;

class UpdateProfileRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'nullable', 'email', Rule::unique('users', 'email')->ignore($this->user()?->id)],
            'bio' => 'nullable|string|max:500',
            'birthday' => 'nullable|date',
            'settings' => 'sometimes|array',
            'avatar' => 'nullable|image|max:2048',
        ];
    }
}

