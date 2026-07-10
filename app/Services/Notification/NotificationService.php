<?php

namespace App\Services\Notification;

use App\Contracts\Services\NotificationServiceInterface;
use App\Models\Notification;
use App\Models\NotificationToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService implements NotificationServiceInterface
{
    public function registerToken(User $user, string $token, string $platform, array $metadata = []): void
    {
        NotificationToken::updateOrCreate(
            ['token' => $token],
            [
                'user_id' => $user->id,
                'platform' => $platform,
                'device_name' => $metadata['device_name'] ?? null,
                'device_id' => $metadata['device_id'] ?? null,
                'app_version' => $metadata['app_version'] ?? null,
                'os_version' => $metadata['os_version'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );
    }

    public function unregisterToken(User $user, string $token): void
    {
        NotificationToken::where('user_id', $user->id)
            ->where('token', $token)
            ->update(['is_active' => false]);
    }

    public function sendPush(User $user, string $title, string $body, array $data = []): bool
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => $data['type'] ?? 'system',
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'sent_at' => now(),
        ]);

        if (config('tuko.notifications.driver', 'fake') === 'fake') {
            Log::info('Fake push notification sent', [
                'user_id' => $user->id,
                'title' => $title,
                'data' => $data,
            ]);

            return true;
        }

        Log::warning('No real push notification driver is configured', [
            'driver' => config('tuko.notifications.driver'),
        ]);

        return false;
    }

    public function sendWidgetUpdate(User $user, int $version, string $eventType): bool
    {
        return $this->sendPush($user, 'Widget updated', 'Your widget has a new update.', [
            'type' => 'widget_update',
            'version' => $version,
            'event_type' => $eventType,
        ]);
    }

    public function sendPartnerNotification(User $sender, User $receiver, string $type, array $data = []): bool
    {
        $title = $data['title'] ?? "{$sender->name} sent you an update";
        $body = $data['body'] ?? 'Open the app to view it.';

        return $this->sendPush($receiver, $title, $body, array_merge($data, [
            'type' => $type,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
        ]));
    }

    public function getTokensForUser(User $user): array
    {
        return $user->notificationTokens()
            ->where('is_active', true)
            ->pluck('token')
            ->all();
    }
}
