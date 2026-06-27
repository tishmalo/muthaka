<?php

namespace App\Contracts\Services;

use App\Models\MoodEvent;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface MoodServiceInterface
{
    public function sendMood(User $user, string $moodType, ?string $notes = null): MoodEvent;
    public function getHistory(User $user, int $limit = 20): LengthAwarePaginator;
    public function getUnseen(User $user): array;
    public function markSeen(User $user): void;
    public function getLatestForPartner(User $user): ?MoodEvent;
}