<?php

namespace App\Broadcasting;

use App\Events\CoupleUpdated;
use App\Models\Couple;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Broadcasts couple realtime events without ever breaking the request.
 * The websocket server (Reverb) may be unreachable in local/dev
 * environments — that must degrade to a log line, not a 4xx/5xx.
 */
class CoupleBroadcaster
{
    public function updated(Couple|string $couple, string $type): void
    {
        $coupleId = $couple instanceof Couple ? $couple->id : $couple;

        try {
            broadcast(new CoupleUpdated($coupleId, $type));
        } catch (Throwable $e) {
            Log::warning('Realtime broadcast failed', [
                'couple_id' => $coupleId,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
