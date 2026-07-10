<?php

namespace App\Services\Mood;

use App\Contracts\Services\MoodServiceInterface;
use App\Jobs\SendPartnerNotification;
use App\Models\MoodEvent;
use App\Models\User;
use App\Services\Support\CoupleContextService;
use App\Services\Widget\WidgetStateService;
use Illuminate\Pagination\LengthAwarePaginator;

class MoodService implements MoodServiceInterface
{
    public function __construct(
        private readonly CoupleContextService $couples,
        private readonly WidgetStateService $widgets,
    ) {
    }

    public function sendMood(User $user, string $moodType, ?string $notes = null): MoodEvent
    {
        $couple = $this->couples->requireActiveCouple($user);
        $partner = $this->couples->requirePartner($user, $couple);

        $mood = MoodEvent::create([
            'couple_id' => $couple->id,
            'sender_id' => $user->id,
            'receiver_id' => $partner->id,
            'mood_type' => $moodType,
            'notes' => $notes,
        ]);

        $this->widgets->updateCoupleForEvent($couple, 'mood', $mood->id);
        SendPartnerNotification::dispatch($user, $partner, 'mood', [
            'title' => "{$user->name} shared a mood",
            'body' => 'Open Tuko to see it.',
            'mood_id' => $mood->id,
        ]);

        return $mood;
    }

    public function getHistory(User $user, int $limit = 20): LengthAwarePaginator
    {
        $couple = $this->couples->requireActiveCouple($user);

        return MoodEvent::where('couple_id', $couple->id)
            ->with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
            ->latest('created_at')
            ->paginate(min(max($limit, 1), 100));
    }

    public function getUnseen(User $user): array
    {
        return MoodEvent::where('receiver_id', $user->id)
            ->where('is_seen', false)
            ->latest('created_at')
            ->get()
            ->all();
    }

    public function markSeen(User $user): void
    {
        MoodEvent::where('receiver_id', $user->id)
            ->where('is_seen', false)
            ->update(['is_seen' => true, 'seen_at' => now()]);
    }

    public function getLatestForPartner(User $user): ?MoodEvent
    {
        $couple = $this->couples->requireActiveCouple($user);
        $partner = $this->couples->requirePartner($user, $couple);

        return MoodEvent::where('couple_id', $couple->id)
            ->where('sender_id', $partner->id)
            ->latest('created_at')
            ->first();
    }
}
