<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Countdown;
use App\Services\Support\CoupleContextService;
use App\Services\Widget\WidgetStateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CountdownController extends Controller
{
    public function __construct(
        private readonly CoupleContextService $couples,
        private readonly WidgetStateService $widgets,
    ) {
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $couple = $this->couples->requireActiveCouple($request->user());
            $countdown = Countdown::create(array_merge($validator->validated(), [
                'couple_id' => $couple->id,
                'user_id' => $request->user()->id,
            ]));

            if ($countdown->is_active) {
                $this->widgets->setActiveCountdown($couple, $countdown->id);
            }

            return ApiResponse::success(['countdown' => $countdown], 'Countdown created successfully', 201);
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
            $couple = $this->couples->requireActiveCouple($request->user());

            return ApiResponse::success([
                'countdowns' => Countdown::where('couple_id', $couple->id)->orderBy('event_date')->get(),
            ]);
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function active(Request $request)
    {
        try {
            $couple = $this->couples->requireActiveCouple($request->user());

            return ApiResponse::success([
                'countdowns' => Countdown::where('couple_id', $couple->id)->where('is_active', true)->orderBy('event_date')->get(),
            ]);
        } catch (Throwable $e) {
            return ApiResponse::forbidden($e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), $this->rules(true));

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $countdown = $this->countdownForUser($request, $id);
        if (!$countdown) {
            return ApiResponse::notFound('Countdown not found');
        }

        $countdown->update($validator->validated());

        if ($countdown->is_active) {
            $this->widgets->setActiveCountdown($countdown->couple, $countdown->id);
        } else {
            $this->widgets->incrementVersion($request->user());
        }

        return ApiResponse::success(['countdown' => $countdown->fresh()], 'Countdown updated successfully');
    }

    public function destroy(Request $request, string $id)
    {
        $countdown = $this->countdownForUser($request, $id);
        if (!$countdown) {
            return ApiResponse::notFound('Countdown not found');
        }

        $couple = $countdown->couple;
        $countdown->delete();
        $this->widgets->setActiveCountdown($couple, null);

        return ApiResponse::success(null, 'Countdown deleted successfully');
    }

    private function countdownForUser(Request $request, string $id): ?Countdown
    {
        try {
            $couple = $this->couples->requireActiveCouple($request->user());
        } catch (Throwable) {
            return null;
        }

        return Countdown::where('id', $id)->where('couple_id', $couple->id)->first();
    }

    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'event_name' => $required.'|string|max:255',
            'event_date' => $required.'|date',
            'background_color' => 'nullable|string|max:7',
            'icon_emoji' => 'nullable|string|max:10',
            'is_active' => 'sometimes|boolean',
            'is_birthday' => 'sometimes|boolean',
        ];
    }
}
