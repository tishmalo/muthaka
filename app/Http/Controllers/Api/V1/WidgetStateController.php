<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CoupleUser;
use App\Models\WidgetState;
use Illuminate\Http\Request;

class WidgetStateController extends Controller
{
    public function index(Request $request)
    {
        $state = $this->stateFor($request);

        if (!$state) {
            return ApiResponse::notFound('No active couple found');
        }

        return ApiResponse::success(['widget_state' => $state->load(['latestMood', 'latestNote', 'activeCountdown'])]);
    }

    public function version(Request $request)
    {
        $state = $this->stateFor($request);

        return ApiResponse::success(['version' => $state?->version ?? 0]);
    }

    public function check(Request $request)
    {
        $state = $this->stateFor($request);

        return response('', 204)->header('X-Widget-Version', (string) ($state?->version ?? 0));
    }

    private function stateFor(Request $request): ?WidgetState
    {
        $coupleUser = CoupleUser::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->with('couple')
            ->first();

        if (!$coupleUser || !$coupleUser->couple) {
            return null;
        }

        $partner = $coupleUser->couple->getPartnerFor($request->user());

        if (!$partner) {
            return null;
        }

        return WidgetState::firstOrCreate(
            ['couple_id' => $coupleUser->couple_id, 'user_id' => $request->user()->id],
            [
                'partner_id' => $partner->id,
                'version' => 0,
                'summary' => [],
                'updated_at' => now(),
            ]
        );
    }
}
