<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCountdownRequest;
use App\Http\Requests\Api\V1\UpdateCountdownRequest;
use App\Models\Countdown;
use App\Services\Support\CoupleContextService;
use App\Services\Widget\WidgetStateService;
use Illuminate\Http\Request;
use Throwable;

class CountdownController extends Controller
{
    public function __construct(
        private readonly CoupleContextService $couples,
        private readonly WidgetStateService $widgets,
    ) {
    }

    public function store(StoreCountdownRequest $request)
    {
        try {
            $couple = $this->couples->requireActiveCouple($request->user());
            $countdown = Countdown::create(array_merge($request->validated(), [
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

    public function update(UpdateCountdownRequest $request, string $id)
    {
        $countdown = $this->countdownForUser($request, $id);
        if (!$countdown) {
            return ApiResponse::notFound('Countdown not found');
        }

        $countdown->update($request->validated());

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
}

