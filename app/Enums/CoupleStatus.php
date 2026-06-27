<?php

namespace App\Enums;

enum CoupleStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
    case DISCONNECTED = 'disconnected';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::ACTIVE => 'Active',
            self::BLOCKED => 'Blocked',
            self::DISCONNECTED => 'Disconnected',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canInteract(): bool
    {
        return in_array($this, [self::ACTIVE]);
    }
}