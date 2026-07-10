<?php

namespace App\Services\Support;

use App\Models\Couple;
use App\Models\CoupleUser;
use App\Models\User;
use RuntimeException;

class CoupleContextService
{
    public function activeCouple(User $user): ?Couple
    {
        return CoupleUser::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('couple.partnerOne', 'couple.partnerTwo')
            ->first()?->couple;
    }

    public function requireActiveCouple(User $user): Couple
    {
        $couple = $this->activeCouple($user);

        if (!$couple || !$couple->is_active) {
            throw new RuntimeException('No active couple found');
        }

        return $couple;
    }

    public function requirePartner(User $user, Couple $couple): User
    {
        $partner = $couple->getPartnerFor($user);

        if (!$partner) {
            throw new RuntimeException('No active partner found');
        }

        return $partner;
    }
}
