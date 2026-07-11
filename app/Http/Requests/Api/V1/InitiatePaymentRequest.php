<?php

namespace App\Http\Requests\Api\V1;

class InitiatePaymentRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|size:3',
            'plan' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:50',
            'phone_number' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:255',
        ];
    }
}

