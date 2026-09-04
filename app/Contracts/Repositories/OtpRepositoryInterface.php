<?php

namespace App\Contracts\Repositories;

use App\Models\PhoneVerification;

interface OtpRepositoryInterface
{
    public function deleteFor(string $identifier, string $type): void;
    public function create(array $data): PhoneVerification;
    public function latestValid(string $identifier, string $type): ?PhoneVerification;
    public function delete(PhoneVerification $verification): void;
}
