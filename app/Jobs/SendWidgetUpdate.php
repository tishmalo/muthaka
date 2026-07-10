<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWidgetUpdate implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public int $version,
        public string $eventType,
    ) {
    }

    public function handle(NotificationService $notifications): void
    {
        $notifications->sendWidgetUpdate($this->user, $this->version, $this->eventType);
    }
}
