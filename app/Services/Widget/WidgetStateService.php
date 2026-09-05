<?php

namespace App\Services\Widget;

use App\Broadcasting\CoupleBroadcaster;
use App\Contracts\Services\WidgetStateServiceInterface;
use App\Models\Couple;
use App\Models\CoupleUser;
use App\Models\User;
use App\Models\WidgetState;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class WidgetStateService implements WidgetStateServiceInterface
{
    public function __construct(private readonly CoupleBroadcaster $broadcast) {}

    public function getForUser(User $user): ?WidgetState
    {
        $couple = $this->activeCouple($user);

        if (! $couple) {
            return null;
        }

        $key = "widget_state:{$couple->id}:{$user->id}";

        $state = Cache::remember($key, now()->addMinutes(5), fn () => $this->freshState($user));

        if (! $state instanceof WidgetState) {
            // Poisoned cache entry (e.g. a stale serialized payload from a
            // previous deploy unserializing to __PHP_Incomplete_Class):
            // drop it and rebuild instead of fataling on the return type.
            Cache::forget($key);
            $state = $this->freshState($user);
            Cache::put($key, $state, now()->addMinutes(5));
        }

        return $state;
    }

    public function getLatestVersion(User $user): int
    {
        return $this->getForUser($user)?->version ?? 0;
    }

    public function updateForEvent(User $user, string $eventType, string $eventId): WidgetState
    {
        $state = $this->createForCouple($user);
        $field = $this->fieldForEvent($eventType);

        $state->forceFill([
            $field => $eventId,
            'version' => $state->version + 1,
            'updated_at' => now(),
        ])->save();

        $this->forget($state);

        return $state->fresh();
    }

    public function incrementVersion(User $user): WidgetState
    {
        $state = $this->createForCouple($user);
        $state->forceFill([
            'version' => $state->version + 1,
            'updated_at' => now(),
        ])->save();

        $this->forget($state);

        return $state->fresh();
    }

    public function createForCouple(User $user): WidgetState
    {
        $couple = $this->activeCouple($user);

        if (! $couple) {
            throw new RuntimeException('No active couple found');
        }

        $partner = $couple->getPartnerFor($user);

        if (! $partner) {
            throw new RuntimeException('No active partner found');
        }

        return WidgetState::firstOrCreate(
            ['couple_id' => $couple->id, 'user_id' => $user->id],
            [
                'partner_id' => $partner->id,
                'version' => 0,
                'summary' => [],
                'updated_at' => now(),
            ]
        );
    }

    public function getSummary(User $user): array
    {
        $state = $this->getForUser($user);

        if (! $state) {
            return ['version' => 0, 'has_couple' => false];
        }

        return [
            'has_couple' => true,
            'version' => $state->version,
            'updated_at' => $state->updated_at,
            'latest_mood_event_id' => $state->latest_mood_event_id,
            'latest_note_event_id' => $state->latest_note_event_id,
            'latest_doodle_event_id' => $state->latest_doodle_event_id,
            'latest_snap_event_id' => $state->latest_snap_event_id,
            'latest_distance_event_id' => $state->latest_distance_event_id,
            'active_countdown_id' => $state->active_countdown_id,
        ];
    }

    public function updateCoupleForEvent(Couple $couple, string $eventType, string $eventId): void
    {
        $couple->users()->wherePivot('status', 'active')->get()->each(
            fn (User $user) => $this->updateForEvent($user, $eventType, $eventId)
        );

        $this->broadcast->updated($couple->id, $eventType);
    }

    public function setActiveCountdown(Couple $couple, ?string $countdownId): void
    {
        WidgetState::where('couple_id', $couple->id)->get()->each(function (WidgetState $state) use ($countdownId) {
            $state->forceFill([
                'active_countdown_id' => $countdownId,
                'version' => $state->version + 1,
                'updated_at' => now(),
            ])->save();

            $this->forget($state);
        });
    }

    private function freshState(User $user): WidgetState
    {
        return $this->createForCouple($user)->load([
            'latestMood',
            'latestNote',
            'activeCountdown',
            'partner:id,name,avatar,last_active_at',
        ]);
    }

    private function activeCouple(User $user): ?Couple
    {
        return CoupleUser::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('couple.partnerOne', 'couple.partnerTwo')
            ->first()?->couple;
    }

    private function fieldForEvent(string $eventType): string
    {
        return match ($eventType) {
            'mood' => 'latest_mood_event_id',
            'note' => 'latest_note_event_id',
            'doodle' => 'latest_doodle_event_id',
            'snap' => 'latest_snap_event_id',
            'distance' => 'latest_distance_event_id',
            default => throw new RuntimeException("Unsupported widget event type [{$eventType}]"),
        };
    }

    private function forget(WidgetState $state): void
    {
        Cache::forget("widget_state:{$state->couple_id}:{$state->user_id}");
    }
}
