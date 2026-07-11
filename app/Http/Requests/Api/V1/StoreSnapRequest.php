<?php

namespace App\Http\Requests\Api\V1;

class StoreSnapRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'image' => 'required|file',
            'duration' => 'nullable|integer|min:1|max:60',
        ];
    }
}

