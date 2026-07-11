<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\MoodType;
use Illuminate\Validation\Rule;

class StoreMoodRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'mood_type' => ['required', 'string', Rule::in(array_column(MoodType::cases(), 'value'))],
            'notes' => 'nullable|string|max:1000',
        ];
    }
}

