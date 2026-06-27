<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Countdown;
use App\Models\Couple;
use App\Models\CoupleUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CountdownController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'background_color' => 'nullable|string|max:7',
            'icon_emoji' => 'nullable|string|max:10',
            'is_active' => 'sometimes|boolean',
            'is_birthday' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $couple = $this->activeCouple($request);
        if (!$couple) {
            return ApiResponse::error('No active couple found', null, 400);
        }

        $countdown = Countdown::create(array_merge($request->only([
            'event_name',
            'event_date',
            'background_color',
            'icon_emoji',
            'is_active',
            'is_birthday',
        ]), [
            'couple_id' => $couple->id,
            'user_id' => $request->user()->id,
        ]));

        return ApiResponse::success(['countdown' => $countdown], 'Countdown created successfully', 201);
    }

    public function index(Request $request)
    {
        $couple = $this->activeCouple($request);
        if (!$couple) {
            return ApiResponse::notFound('No active couple found');
        }

        $countdowns = Countdown::where('couple_id', $couple->id)
            ->orderBy('event_date')
            ->get();

        return ApiResponse::success(['countdowns' => $countdowns]);
    }

    public function active(Request $request)
    {
        $couple = $this->activeCouple($request);
        if (!$couple) {
            return ApiResponse::notFound('No active couple found');
        }

        $countdowns = Countdown::where('couple_id', $couple->id)
            ->where('is_active', true)
            ->orderBy('event_date')
            ->get();

        return ApiResponse::success(['countdowns' => $countdowns]);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'event_name' => 'sometimes|string|max:255',
            'event_date' => 'sometimes|date',
            'background_color' => 'nullable|string|max:7',
            'icon_emoji' => 'nullable|string|max:10',
            'is_active' => 'sometimes|boolean',
            'is_birthday' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $countdown = $this->countdownForUser($request, $id);
        if (!$countdown) {
            return ApiResponse::notFound('Countdown not found');
        }

        $countdown->update($request->only([
            'event_name',
            'event_date',
            'background_color',
            'icon_emoji',
            'is_active',
            'is_birthday',
        ]));

        return ApiResponse::success(['countdown' => $countdown->fresh()], 'Countdown updated successfully');
    }

    public function destroy(Request $request, string $id)
    {
        $countdown = $this->countdownForUser($request, $id);
        if (!$countdown) {
            return ApiResponse::notFound('Countdown not found');
        }

        $countdown->delete();

        return ApiResponse::success(null, 'Countdown deleted successfully');
    }

    private function activeCouple(Request $request): ?Couple
    {
        return CoupleUser::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->with('couple')
            ->first()?->couple;
    }

    private function countdownForUser(Request $request, string $id): ?Countdown
    {
        $couple = $this->activeCouple($request);
        if (!$couple) {
            return null;
        }

        return Countdown::where('id', $id)
            ->where('couple_id', $couple->id)
            ->first();
    }
}
