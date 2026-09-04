<?php

namespace App\Repositories;

use App\Contracts\Repositories\OtpRepositoryInterface;
use App\Models\PhoneVerification;

class OtpRepository implements OtpRepositoryInterface
{
    public function deleteFor(string $identifier, string $type): void
    {
        PhoneVerification::where('phone_number', $identifier)
            ->where('type', $type)
            ->delete();
    }

    public function create(array $data): PhoneVerification
    {
        return PhoneVerification::create($data);
    }

    public function latestValid(string $identifier, string $type): ?PhoneVerification
    {
        return PhoneVerification::where('phone_number', $identifier)
            ->where('type', $type)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function delete(PhoneVerification $verification): void
    {
        $verification->delete();
    }
}
