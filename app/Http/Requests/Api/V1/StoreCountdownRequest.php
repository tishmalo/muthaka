<?php

namespace App\Http\Requests\Api\V1;

class StoreCountdownRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'background_color' => 'nullable|string|max:7',
            'icon_emoji' => 'nullable|string|max:10',
            'is_active' => 'sometimes|boolean',
            'is_birthday' => 'sometimes|boolean',
        ];
    }
}

