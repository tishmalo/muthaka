<?php

namespace App\Http\Requests\Api\V1;

class CoupleInviteRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            // Code-first pairing: email is optional. With an email the invite
            // is also mailed; without one the app shares the code itself.
            'email' => 'nullable|email',
        ];
    }
}

