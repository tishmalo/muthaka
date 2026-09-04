<?php

namespace App\Repositories;

use App\Contracts\Repositories\CoupleRepositoryInterface;
use App\Contracts\Repositories\CoupleUserRepositoryInterface;
use App\Models\Couple;
use App\Models\User;

class CoupleRepository implements CoupleRepositoryInterface
{
    public function __construct(private readonly CoupleUserRepositoryInterface $coupleUsers)
    {
    }

    public function create(array $data): Couple
    {
        return Couple::create($data);
    }

    public function update(Couple $couple, array $data): Couple
    {
        $couple->update($data);

        return $couple->fresh();
    }

    public function findActiveForUser(User $user): ?Couple
    {
        return $this->coupleUsers->findActiveForUser($user->id)?->couple;
    }
}
