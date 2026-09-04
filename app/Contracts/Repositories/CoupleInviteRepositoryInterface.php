<?php

namespace App\Contracts\Repositories;

use App\Models\CoupleInvite;

interface CoupleInviteRepositoryInterface
{
    public function findPendingForInviterEmail(string $inviterId, string $email): ?CoupleInvite;
    public function findPendingByCode(string $code): ?CoupleInvite;
    public function findPendingByInviter(string $inviterId): ?CoupleInvite;
    public function create(array $data): CoupleInvite;
    public function generateCode(): string;
}
