<?php

namespace App\Repositories;

use App\Contracts\Repositories\CoupleUserRepositoryInterface;
use App\Models\Couple;
use App\Models\CoupleUser;

class CoupleUserRepository implements CoupleUserRepositoryInterface
{
    public function findActiveForUser(string $userId): ?CoupleUser
    {
        return CoupleUser::where('user_id', $userId)
            ->where('status', 'active')
            ->with('couple')
            ->first();
    }

    public function create(array $data): CoupleUser
    {
        return CoupleUser::create($data);
    }

    public function markAllLeft(Couple $couple, string $status): void
    {
        CoupleUser::where('couple_id', $couple->id)->update([
            'status' => $status,
            'left_at' => now(),
        ]);
    }
}
