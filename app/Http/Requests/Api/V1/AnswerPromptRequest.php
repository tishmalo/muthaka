<?php

namespace App\Http\Requests\Api\V1;

class AnswerPromptRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'answer' => 'required|string|max:5000',
            'reaction' => 'nullable|integer|min:1|max:5',
        ];
    }
}

