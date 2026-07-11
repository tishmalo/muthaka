<?php

namespace App\Http\Requests\Api\V1;

class StoreNoteRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'content' => 'required|string|max:5000',
        ];
    }
}

