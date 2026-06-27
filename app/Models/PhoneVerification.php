<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'code',
        'type',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerification(): bool
    {
        return $this->type === 'verification';
    }

    public function isPasswordReset(): bool
    {
        return $this->type === 'password_reset';
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeByPhone($query, string $phone)
    {
        return $query->where('phone_number', $phone);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }
}