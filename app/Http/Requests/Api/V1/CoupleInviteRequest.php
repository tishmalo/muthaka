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
            // Invite-first: the invitee may not be registered yet — they
            // register with this email and accept the code later.
            'email' => 'required|email',
        ];
    }
}

