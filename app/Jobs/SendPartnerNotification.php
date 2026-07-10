<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPartnerNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $sender,
        public User $receiver,
        public string $type,
        public array $data = [],
    ) {
    }

    public function handle(NotificationService $notifications): void
    {
        $notifications->sendPartnerNotification($this->sender, $this->receiver, $this->type, $this->data);
    }
}
