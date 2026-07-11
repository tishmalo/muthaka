<?php

namespace App\Http\Requests\Api\V1;

class StoreDoodleRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'image' => 'required|file',
            'duration' => 'nullable|integer|min:1|max:3600',
            'stroke_count' => 'nullable|integer|min:0',
            'metadata' => 'nullable|array',
        ];
    }
}

