<?php

namespace App\Services\Media;

use App\Jobs\SendPartnerNotification;
use App\Models\Snap;
use App\Models\SnapView;
use App\Models\User;
use App\Services\Support\CoupleContextService;
use App\Services\Widget\WidgetStateService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

class SnapService
{
    public function __construct(
        private readonly CoupleContextService $couples,
        private readonly MediaService $media,
        private readonly WidgetStateService $widgets,
    ) {
    }

    public function send(User $user, UploadedFile $image, array $data = []): Snap
    {
        if (!$user->canSendSnap()) {
            throw new RuntimeException('Daily snap limit reached');
        }

        $couple = $this->couples->requireActiveCouple($user);
        $partner = $this->couples->requirePartner($user, $couple);
        $stored = $this->media->storeImage($image, "couples/{$couple->id}/snaps");

        $snap = Snap::create([
            'couple_id' => $couple->id,
            'sender_id' => $user->id,
            'receiver_id' => $partner->id,
            'image_path' => $stored['image_path'],
            'thumbnail_path' => $stored['thumbnail_path'],
            'duration' => $data['duration'] ?? 10,
            'expires_at' => now()->addHours((int) config('tuko.media.snap_expiry_hours', 24)),
        ]);

        $this->widgets->updateCoupleForEvent($couple, 'snap', $snap->id);
        SendPartnerNotification::dispatch($user, $partner, 'snap', [
            'title' => "{$user->name} sent a snap",
            'body' => 'Open Tuko to view it.',
            'snap_id' => $snap->id,
        ]);

        return $snap;
    }

    public function history(User $user, int $limit = 20): LengthAwarePaginator
    {
        $couple = $this->couples->requireActiveCouple($user);

        return Snap::where('couple_id', $couple->id)
            ->unexpired()
            ->with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
            ->latest('created_at')
            ->paginate(min(max($limit, 1), 100));
    }

    public function unseen(User $user)
    {
        return Snap::where('receiver_id', $user->id)
            ->where('is_seen', false)
            ->unexpired()
            ->latest('created_at')
            ->get();
    }

    public function view(User $user, string $id): Snap
    {
        $snap = Snap::where('id', $id)
            ->where('receiver_id', $user->id)
            ->unexpired()
            ->first();

        if (!$snap) {
            throw new RuntimeException('Snap not found');
        }

        $snap->markAsSeen();
        SnapView::firstOrCreate([
            'snap_id' => $snap->id,
            'viewer_id' => $user->id,
        ]);

        return $snap->fresh();
    }

    public function delete(User $user, string $id): void
    {
        $snap = Snap::where('id', $id)->where('sender_id', $user->id)->first();

        if (!$snap) {
            throw new RuntimeException('Snap not found');
        }

        $this->media->delete($snap->image_path);
        $this->media->delete($snap->thumbnail_path);
        $snap->delete();
    }
}
