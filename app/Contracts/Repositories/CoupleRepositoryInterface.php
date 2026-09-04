<?php

namespace App\Contracts\Repositories;

use App\Models\Couple;
use App\Models\User;

interface CoupleRepositoryInterface
{
    public function create(array $data): Couple;
    public function update(Couple $couple, array $data): Couple;
    public function findActiveForUser(User $user): ?Couple;
}
