<?php

namespace App\Services\Distance;

use App\Models\DistanceEvent;
use App\Models\User;
use App\Services\Support\CoupleContextService;
use App\Services\Widget\WidgetStateService;
use Illuminate\Pagination\LengthAwarePaginator;

class DistanceService
{
    public function __construct(
        private readonly CoupleContextService $couples,
        private readonly WidgetStateService $widgets,
    ) {
    }

    public function updateLocation(User $user, array $data): DistanceEvent
    {
        $couple = $this->couples->requireActiveCouple($user);
        $partner = $this->couples->requirePartner($user, $couple);
        $isSharing = (bool) ($data['is_sharing'] ?? true);
        $partnerLocation = DistanceEvent::where('couple_id', $couple->id)
            ->where('user_id', $partner->id)
            ->where('is_sharing', true)
            ->latest('recorded_at')
            ->first();

        $event = DistanceEvent::create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'accuracy' => $data['accuracy'] ?? null,
            'place_name' => $data['place_name'] ?? null,
            'address' => $data['address'] ?? null,
            'is_sharing' => $isSharing,
            'distance_to_partner' => $isSharing && $partnerLocation
                ? $this->calculateDistance(
                    (float) $data['latitude'],
                    (float) $data['longitude'],
                    (float) $partnerLocation->latitude,
                    (float) $partnerLocation->longitude,
                )
                : null,
        ]);

        $this->widgets->updateCoupleForEvent($couple, 'distance', $event->id);

        return $event;
    }

    public function current(User $user): array
    {
        $couple = $this->couples->requireActiveCouple($user);
        $partner = $this->couples->requirePartner($user, $couple);

        return [
            'mine' => DistanceEvent::where('couple_id', $couple->id)->where('user_id', $user->id)->latest('recorded_at')->first(),
            'partner' => DistanceEvent::where('couple_id', $couple->id)->where('user_id', $partner->id)->where('is_sharing', true)->latest('recorded_at')->first(),
        ];
    }

    public function history(User $user, int $limit = 20): LengthAwarePaginator
    {
        $couple = $this->couples->requireActiveCouple($user);

        return DistanceEvent::where('couple_id', $couple->id)
            ->with('user:id,name,avatar')
            ->latest('recorded_at')
            ->paginate(min(max($limit, 1), 100));
    }

    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;

        return round($earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }
}
