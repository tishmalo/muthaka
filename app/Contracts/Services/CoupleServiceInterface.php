<?php

namespace App\Contracts\Services;

use App\Models\Couple;
use App\Models\User;

interface CoupleServiceInterface
{
    public function createInvite(User $user, string $inviteePhone): array;
    public function acceptInvite(User $user, string $inviteCode): Couple;
    public function rejectInvite(User $user, string $inviteCode): void;
    public function cancelInvite(User $user): void;
    public function disconnect(User $user, ?string $reason = null): void;
    public function blockPartner(User $user): void;
    public function getCoupleStatus(User $user): array;
    public function getPartner(User $user): ?User;
    public function isInActiveCouple(User $user): bool;
    public function getActiveCouple(User $user): ?Couple;
}