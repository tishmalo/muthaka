<?php

namespace App\Contracts\Repositories;

use App\Models\Couple;
use App\Models\CoupleUser;

interface CoupleUserRepositoryInterface
{
    public function findActiveForUser(string $userId): ?CoupleUser;
    public function create(array $data): CoupleUser;
    public function markAllLeft(Couple $couple, string $status): void;
}
