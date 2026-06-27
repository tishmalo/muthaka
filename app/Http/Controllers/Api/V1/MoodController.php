<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MoodType;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Couple;
use App\Models\CoupleUser;
use App\Models\MoodEvent;
use App\Models\WidgetState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MoodController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mood_type' => ['required', 'string', Rule::in(array_column(MoodType::cases(), 'value'))],
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $couple = $this->activeCouple($request);
        $partner = $couple?->getPartnerFor($request->user());

        if (!$couple || !$partner) {
            return ApiResponse::error('No active couple found', null, 400);
        }

        $mood = MoodEvent::create([
            'couple_id' => $couple->id,
            'sender_id' => $request->user()->id,
            'receiver_id' => $partner->id,
            'mood_type' => $request->mood_type,
            'notes' => $request->notes,
        ]);

        $this->touchWidgets($couple, 'mood', $mood->id);

        return ApiResponse::success(['mood' => $mood], 'Mood sent successfully', 201);
    }

    public function index(Request $request)
    {
        $couple = $this->activeCouple($request);
        if (!$couple) {
            return ApiResponse::notFound('No active couple found');
        }

        $perPage = min((int) $request->query('per_page', 20), 100);
        $moods = MoodEvent::where('couple_id', $couple->id)
            ->with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
            ->latest('created_at')
            ->paginate($perPage);

        return ApiResponse::paginated($moods);
    }

    public function unseen(Request $request)
    {
        $moods = MoodEvent::where('receiver_id', $request->user()->id)
            ->where('is_seen', false)
            ->latest('created_at')
            ->get();

        return ApiResponse::success(['moods' => $moods]);
    }

    public function markSeen(Request $request)
    {
        MoodEvent::where('receiver_id', $request->user()->id)
            ->where('is_seen', false)
            ->update(['is_seen' => true, 'seen_at' => now()]);

        return ApiResponse::success(null, 'Moods marked as seen');
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
