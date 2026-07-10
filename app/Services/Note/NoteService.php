<?php

namespace App\Services\Note;

use App\Contracts\Services\NoteServiceInterface;
use App\Jobs\SendPartnerNotification;
use App\Models\NoteEvent;
use App\Models\User;
use App\Services\Support\CoupleContextService;
use App\Services\Widget\WidgetStateService;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

class NoteService implements NoteServiceInterface
{
    public function __construct(
        private readonly CoupleContextService $couples,
        private readonly WidgetStateService $widgets,
    ) {
    }

    public function sendNote(User $user, string $content): NoteEvent
    {
        $couple = $this->couples->requireActiveCouple($user);
        $partner = $this->couples->requirePartner($user, $couple);

        $note = NoteEvent::create([
            'couple_id' => $couple->id,
            'sender_id' => $user->id,
            'receiver_id' => $partner->id,
            'content' => $content,
        ]);

        $this->widgets->updateCoupleForEvent($couple, 'note', $note->id);
        SendPartnerNotification::dispatch($user, $partner, 'note', [
            'title' => "{$user->name} sent you a note",
            'body' => 'Open Tuko to read it.',
            'note_id' => $note->id,
        ]);

        return $note;
    }

    public function getHistory(User $user, int $limit = 20): LengthAwarePaginator
    {
        $couple = $this->couples->requireActiveCouple($user);

        return NoteEvent::where('couple_id', $couple->id)
            ->with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
            ->latest('created_at')
            ->paginate(min(max($limit, 1), 100));
    }

    public function getUnseen(User $user): array
    {
        return NoteEvent::where('receiver_id', $user->id)
            ->where('is_seen', false)
            ->latest('created_at')
            ->get()
            ->all();
    }

    public function markSeen(User $user, string $noteId): void
    {
        $note = NoteEvent::where('id', $noteId)
            ->where('receiver_id', $user->id)
            ->first();

        if (!$note) {
            throw new RuntimeException('Note not found');
        }

        $note->markAsSeen();
    }

    public function getLatestForPartner(User $user): ?NoteEvent
    {
        $couple = $this->couples->requireActiveCouple($user);
        $partner = $this->couples->requirePartner($user, $couple);

        return NoteEvent::where('couple_id', $couple->id)
            ->where('sender_id', $partner->id)
            ->latest('created_at')
            ->first();
    }
}
