<?php

namespace App\Services\Media;

use App\Jobs\SendPartnerNotification;
use App\Models\Doodle;
use App\Models\User;
use App\Services\Support\CoupleContextService;
use App\Services\Widget\WidgetStateService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

class DoodleService
{
    public function __construct(
        private readonly CoupleContextService $couples,
        private readonly MediaService $media,
        private readonly WidgetStateService $widgets,
    ) {
    }

    public function send(User $user, UploadedFile $image, array $data = []): Doodle
    {
        $couple = $this->couples->requireActiveCouple($user);
        $partner = $this->couples->requirePartner($user, $couple);
        $stored = $this->media->storeImage($image, "couples/{$couple->id}/doodles");

        $doodle = Doodle::create(array_merge($stored, [
            'couple_id' => $couple->id,
            'sender_id' => $user->id,
            'receiver_id' => $partner->id,
            'duration' => $data['duration'] ?? null,
            'stroke_count' => $data['stroke_count'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]));

        $this->widgets->updateCoupleForEvent($couple, 'doodle', $doodle->id);
        SendPartnerNotification::dispatch($user, $partner, 'doodle', [
            'title' => "{$user->name} sent a doodle",
            'body' => 'Open Tuko to view it.',
            'doodle_id' => $doodle->id,
        ]);

        return $doodle;
    }

    public function history(User $user, int $limit = 20): LengthAwarePaginator
    {
        $couple = $this->couples->requireActiveCouple($user);

        return Doodle::where('couple_id', $couple->id)
            ->with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
            ->latest('created_at')
            ->paginate(min(max($limit, 1), 100));
    }

    public function unseen(User $user)
    {
        return Doodle::where('receiver_id', $user->id)
            ->where('is_seen', false)
            ->latest('created_at')
            ->get();
    }

    public function markSeen(User $user, string $id): void
    {
        $doodle = Doodle::where('id', $id)->where('receiver_id', $user->id)->first();

        if (!$doodle) {
            throw new RuntimeException('Doodle not found');
        }

        $doodle->markAsSeen();
    }
}
