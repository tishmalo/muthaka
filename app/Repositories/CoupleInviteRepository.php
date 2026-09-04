<?php

namespace App\Repositories;

use App\Contracts\Repositories\CoupleInviteRepositoryInterface;
use App\Models\CoupleInvite;

class CoupleInviteRepository implements CoupleInviteRepositoryInterface
{
    public function findPendingForInviterEmail(string $inviterId, string $email): ?CoupleInvite
    {
        return CoupleInvite::where('inviter_id', $inviterId)
            ->where('invitee_email', $email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function findPendingByCode(string $code): ?CoupleInvite
    {
        return CoupleInvite::where('invite_code', $code)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function findPendingByInviter(string $inviterId): ?CoupleInvite
    {
        return CoupleInvite::where('inviter_id', $inviterId)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function create(array $data): CoupleInvite
    {
        return CoupleInvite::create($data);
    }

    public function generateCode(): string
    {
        return CoupleInvite::generateInviteCode();
    }
}
