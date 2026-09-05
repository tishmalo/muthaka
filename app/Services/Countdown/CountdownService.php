<?php

namespace App\Services\Countdown;

use App\Broadcasting\CoupleBroadcaster;
use App\Contracts\Repositories\CountdownRepositoryInterface;
use App\Contracts\Services\CountdownServiceInterface;
use App\Models\Countdown;
use App\Models\Couple;
use App\Models\User;
use App\Services\Support\CoupleContextService;
use App\Services\Widget\WidgetStateService;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class CountdownService implements CountdownServiceInterface
{
    public function __construct(
        private readonly CountdownRepositoryInterface $countdowns,
        private readonly CoupleContextService $couples,
        private readonly WidgetStateService $widgets,
        private readonly CoupleBroadcaster $broadcast,
    ) {}

    public function createCountdown(User $user, array $data): Countdown
    {
        $couple = $this->couples->requireActiveCouple($user);

        $countdown = $this->countdowns->create(array_merge($data, [
            'couple_id' => $couple->id,
            'user_id' => $user->id,
        ]));

        if ($countdown->is_active) {
            $this->widgets->setActiveCountdown($couple, $countdown->id);
        }

        $this->broadcast->updated($couple->id, 'countdown');

        return $countdown;
    }

    public function listCountdowns(User $user): Collection
    {
        $couple = $this->couples->requireActiveCouple($user);

        return $this->countdowns->listForCouple($couple->id);
    }

    public function listActiveCountdowns(User $user): Collection
    {
        $couple = $this->couples->requireActiveCouple($user);

        return $this->countdowns->listActiveForCouple($couple->id);
    }

    public function updateCountdown(User $user, string $id, array $data): ?Countdown
    {
        $couple = $this->activeCoupleOrNull($user);

        if (! $couple) {
            return null;
        }

        $countdown = $this->countdowns->findForCouple($id, $couple->id);

        if (! $countdown) {
            return null;
        }

        $countdown = $this->countdowns->update($countdown, $data);

        if ($countdown->is_active) {
            $this->widgets->setActiveCountdown($countdown->couple, $countdown->id);
        } else {
            $this->widgets->incrementVersion($user);
        }

        $this->broadcast->updated($countdown->couple_id, 'countdown');

        return $countdown;
    }

    public function deleteCountdown(User $user, string $id): bool
    {
        $couple = $this->activeCoupleOrNull($user);

        if (! $couple) {
            return false;
        }

        $countdown = $this->countdowns->findForCouple($id, $couple->id);

        if (! $countdown) {
            return false;
        }

        $couple = $countdown->couple;
        $this->countdowns->delete($countdown);
        $this->widgets->setActiveCountdown($couple, null);

        $this->broadcast->updated($couple->id, 'countdown');

        return true;
    }

    private function activeCoupleOrNull(User $user): ?Couple
    {
        try {
            return $this->couples->requireActiveCouple($user);
        } catch (Throwable) {
            return null;
        }
    }
}
