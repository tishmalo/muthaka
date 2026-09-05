<?php

namespace App\Contracts\Repositories;

use App\Models\Countdown;
use Illuminate\Database\Eloquent\Collection;

interface CountdownRepositoryInterface
{
    public function create(array $data): Countdown;

    public function update(Countdown $countdown, array $data): Countdown;

    public function delete(Countdown $countdown): void;

    public function findForCouple(string $id, string $coupleId): ?Countdown;

    public function listForCouple(string $coupleId): Collection;

    public function listActiveForCouple(string $coupleId): Collection;
}
