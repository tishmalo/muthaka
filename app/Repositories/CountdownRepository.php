<?php

namespace App\Repositories;

use App\Contracts\Repositories\CountdownRepositoryInterface;
use App\Models\Countdown;
use Illuminate\Database\Eloquent\Collection;

class CountdownRepository implements CountdownRepositoryInterface
{
    public function create(array $data): Countdown
    {
        return Countdown::create($data);
    }

    public function update(Countdown $countdown, array $data): Countdown
    {
        $countdown->update($data);

        return $countdown->fresh();
    }

    public function delete(Countdown $countdown): void
    {
        $countdown->delete();
    }

    public function findForCouple(string $id, string $coupleId): ?Countdown
    {
        return Countdown::where('id', $id)->where('couple_id', $coupleId)->first();
    }

    public function listForCouple(string $coupleId): Collection
    {
        return Countdown::where('couple_id', $coupleId)->orderBy('event_date')->get();
    }

    public function listActiveForCouple(string $coupleId): Collection
    {
        return Countdown::where('couple_id', $coupleId)
            ->where('is_active', true)
            ->orderBy('event_date')
            ->get();
    }
}
