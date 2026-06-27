<?php

namespace App\Contracts\Services;

use App\Models\User;

interface NotificationServiceInterface
{
    public function registerToken(User $user, string $token, string $platform, array $metadata = []): void;
    public function unregisterToken(User $user, string $token): void;
    public function sendPush(User $user, string $title, string $body, array $data = []): bool;
    public function sendWidgetUpdate(User $user, int $version, string $eventType): bool;
    public function sendPartnerNotification(User $sender, User $receiver, string $type, array $data = []): bool;
    public function getTokensForUser(User $user): array;
}