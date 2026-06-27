<?php

namespace App\Models;

use App\Traits\HasAudit;
use App\Traits\HasCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cache;
class NotificationToken extends Model
{
    use HasFactory, HasAudit, HasCache;

    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'device_name',
        'device_id',
        'app_version',
        'os_version',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeByDevice($query, string $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    public function scopeStale($query, int $days = 30)
    {
        return $query->where('last_used_at', '<', now()->subDays($days));
    }

    // Methods
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function getIsAndroidAttribute(): bool
    {
        return $this->platform === 'android';
    }

    public function getIsIosAttribute(): bool
    {
        return $this->platform === 'ios';
    }

    // Boot method
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->last_used_at)) {
                $model->last_used_at = now();
            }
            if ($model->is_active === null) {
                $model->is_active = true;
            }
        });
    }

    protected function clearModelCache(): void
    {
        Cache::forget('user_' . $this->user_id . '_tokens');
        Cache::forget('token_' . $this->token);
    }
}