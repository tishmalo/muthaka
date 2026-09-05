<?php

namespace App\Contracts\Services;

use App\Models\Countdown;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface CountdownServiceInterface
{
    public function createCountdown(User $user, array $data): Countdown;

    public function listCountdowns(User $user): Collection;

    public function listActiveCountdowns(User $user): Collection;

    public function updateCountdown(User $user, string $id, array $data): ?Countdown;

    public function deleteCountdown(User $user, string $id): bool;
}
