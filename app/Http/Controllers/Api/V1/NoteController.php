<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Couple;
use App\Models\CoupleUser;
use App\Models\NoteEvent;
use App\Models\WidgetState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $couple = $this->activeCouple($request);
        $partner = $couple?->getPartnerFor($request->user());

        if (!$couple || !$partner) {
            return ApiResponse::error('No active couple found', null, 400);
        }

        $note = NoteEvent::create([
            'couple_id' => $couple->id,
            'sender_id' => $request->user()->id,
            'receiver_id' => $partner->id,
            'content' => $request->content,
        ]);

        $this->touchWidgets($couple, 'note', $note->id);

        return ApiResponse::success(['note' => $note], 'Note sent successfully', 201);
    }

    public function index(Request $request)
    {
        $couple = $this->activeCouple($request);
        if (!$couple) {
            return ApiResponse::notFound('No active couple found');
        }

        $perPage = min((int) $request->query('per_page', 20), 100);
        $notes = NoteEvent::where('couple_id', $couple->id)
            ->with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
            ->latest('created_at')
            ->paginate($perPage);

        return ApiResponse::paginated($notes);
    }

    public function unseen(Request $request)
    {
        $notes = NoteEvent::where('receiver_id', $request->user()->id)
            ->where('is_seen', false)
            ->latest('created_at')
            ->get();

        return ApiResponse::success(['notes' => $notes]);
    }

    public function markSeen(Request $request, string $noteId)
    {
        $note = NoteEvent::where('id', $noteId)
            ->where('receiver_id', $request->user()->id)
            ->first();

        if (!$note) {
            return ApiResponse::notFound('Note not found');
        }

        $note->markAsSeen();

        return ApiResponse::success(null, 'Note marked as seen');
    }

    private function activeCouple(Request $request): ?Couple
    {
        return CoupleUser::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->with('couple')
            ->first()?->couple;
    }

    private function touchWidgets(Couple $couple, string $eventType, string $eventId): void
    {
        WidgetState::where('couple_id', $couple->id)->get()->each->updateLatestEvent($eventType, $eventId);
    }
}
