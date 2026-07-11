<?php

namespace App\Http\Requests\Api\V1;

class StoreDistanceRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'place_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_sharing' => 'sometimes|boolean',
        ];
    }
}

