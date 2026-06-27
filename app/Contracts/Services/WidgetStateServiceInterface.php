<?php

namespace App\Contracts\Services;

use App\Models\User;
use App\Models\WidgetState;

interface WidgetStateServiceInterface
{
    public function getForUser(User $user): ?WidgetState;
    public function getLatestVersion(User $user): int;
    public function updateForEvent(User $user, string $eventType, string $eventId): WidgetState;
    public function incrementVersion(User $user): WidgetState;
    public function createForCouple(User $user): WidgetState;
    public function getSummary(User $user): array;
}