<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Fired synchronously (no queue needed) whenever couple-visible content
 * changes: mood|note|snap|doodle|distance|countdown|prompt|couple.
 * Apps subscribe to private-couple.{id} and refresh on receipt.
 */
class CoupleUpdated implements ShouldBroadcastNow
{
    public function __construct(
        public readonly string $coupleId,
        public readonly string $type,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('couple.'.$this->coupleId)];
    }

    public function broadcastAs(): string
    {
        return 'couple.updated';
    }

    /**
     * @return array<string, string>
     */
    public function broadcastWith(): array
    {
        return ['type' => $this->type, 'at' => now()->toIso8601String()];
    }
}
