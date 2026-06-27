<?php

namespace App\Models;

use App\Traits\HasAudit;
use App\Traits\HasCache;
use App\Traits\HasSettings;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cache;

class Couple extends Model
{
    use HasFactory, HasUuid, SoftDeletes;
    use HasSettings, HasAudit, HasCache;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'partner_one_id',
        'partner_two_id',
        'status',
        'connected_at',
        'disconnected_at',
        'disconnect_reason',
        'settings',
        'preferences',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
        'settings' => 'array',
        'preferences' => 'array',
    ];

    // Relationships
    public function partnerOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_one_id');
    }

    public function partnerTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_two_id');
    }

    public function coupleUsers(): HasMany
    {
        return $this->hasMany(CoupleUser::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'couple_user')
            ->withPivot('role', 'status', 'joined_at', 'left_at')
            ->withTimestamps();
    }

    public function widgetStates(): HasMany
    {
        return $this->hasMany(WidgetState::class);
    }

    public function moods(): HasMany
    {
        return $this->hasMany(MoodEvent::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(NoteEvent::class);
    }

    public function doodles(): HasMany
    {
        return $this->hasMany(Doodle::class);
    }

    public function snaps(): HasMany
    {
        return $this->hasMany(Snap::class);
    }

    public function distanceEvents(): HasMany
    {
        return $this->hasMany(DistanceEvent::class);
    }

    public function countdowns(): HasMany
    {
        return $this->hasMany(Countdown::class);
    }

    public function promptAnswers(): HasMany
    {
        return $this->hasMany(PromptAnswer::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Methods
    public function getPartnerFor(User $user): ?User
    {
        if ($this->partner_one_id === $user->id) {
            return $this->partnerTwo;
        }

        if ($this->partner_two_id === $user->id) {
            return $this->partnerOne;
        }

        return null;
    }

    public function isPartner(User $user): bool
    {
        return $this->partner_one_id === $user->id || $this->partner_two_id === $user->id;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'connected_at' => now(),
        ]);
    }

    public function disconnect(string $reason = null): void
    {
        $this->update([
            'status' => 'disconnected',
            'disconnected_at' => now(),
            'disconnect_reason' => $reason,
        ]);
    }

    public function hasWidgetStateFor(User $user): bool
    {
        return $this->widgetStates()->where('user_id', $user->id)->exists();
    }

    public function getWidgetStateFor(User $user): ?WidgetState
    {
        return $this->widgetStates()->where('user_id', $user->id)->first();
    }

    public function getSummary(): array
    {
        $summary = [
            'status' => $this->status,
            'connected_at' => $this->connected_at,
            'moods_count' => $this->moods()->count(),
            'notes_count' => $this->notes()->count(),
            'countdowns_count' => $this->countdowns()->where('is_active', true)->count(),
            'last_activity' => $this->moods()->latest()->first()?->created_at,
        ];

        return $summary;
    }

    protected function clearModelCache(): void
    {
        Cache::forget('couple_' . $this->id . '_summary');
        Cache::forget('couple_' . $this->id . '_partners');
    }
}