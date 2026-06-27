<?php

namespace App\Models;

use App\Traits\HasAudit;
use App\Traits\HasCache;
use App\Traits\HasSettings;
use App\Traits\HasSlug;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Cache;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuid, Notifiable, SoftDeletes;
    use HasSettings, HasAudit, HasCache;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'avatar',
        'bio',
        'birthday',
        'settings',
        'preferences',
        'status',
        'last_active_at',
        'phone_verified_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_active_at' => 'datetime',
        'birthday' => 'date',
        'settings' => 'array',
        'preferences' => 'array',
    ];

    // Relationships
    public function couples(): BelongsToMany
    {
        return $this->belongsToMany(Couple::class, 'couple_user')
            ->withPivot('role', 'status', 'joined_at', 'left_at')
            ->withTimestamps();
    }

    public function activeCouple(): BelongsToMany
    {
        return $this->belongsToMany(Couple::class, 'couple_user')
            ->wherePivot('status', 'active')
            ->wherePivot('role', 'partner')
            ->latest('joined_at')
            ->limit(1);
    }

    public function coupleUsers(): HasMany
    {
        return $this->hasMany(CoupleUser::class);
    }

    public function widgetState(): HasOne
    {
        return $this->hasOne(WidgetState::class);
    }

    public function sentMoods(): HasMany
    {
        return $this->hasMany(MoodEvent::class, 'sender_id');
    }

    public function receivedMoods(): HasMany
    {
        return $this->hasMany(MoodEvent::class, 'receiver_id');
    }

    public function sentNotes(): HasMany
    {
        return $this->hasMany(NoteEvent::class, 'sender_id');
    }

    public function receivedNotes(): HasMany
    {
        return $this->hasMany(NoteEvent::class, 'receiver_id');
    }

    public function sentDoodles(): HasMany
    {
        return $this->hasMany(Doodle::class, 'sender_id');
    }

    public function receivedDoodles(): HasMany
    {
        return $this->hasMany(Doodle::class, 'receiver_id');
    }

    public function sentSnaps(): HasMany
    {
        return $this->hasMany(Snap::class, 'sender_id');
    }

    public function receivedSnaps(): HasMany
    {
        return $this->hasMany(Snap::class, 'receiver_id');
    }

    public function countdowns(): HasMany
    {
        return $this->hasMany(Countdown::class);
    }

    public function distanceEvents(): HasMany
    {
        return $this->hasMany(DistanceEvent::class);
    }

    public function notificationTokens(): HasMany
    {
        return $this->hasMany(NotificationToken::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invitesSent(): HasMany
    {
        return $this->hasMany(CoupleInvite::class, 'inviter_id');
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

    public function scopeVerified($query)
    {
        return $query->whereNotNull('phone_verified_at');
    }

    public function scopeOnline($query)
    {
        return $query->where('last_active_at', '>=', now()->subMinutes(5));
    }

    // Attributes
    public function getIsVerifiedAttribute(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->last_active_at && $this->last_active_at->diffInMinutes(now()) < 5;
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->name);
        $initials = '';
        foreach ($parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper($part[0]);
            }
        }
        return $initials;
    }

    // Methods
    public function hasActiveCouple(): bool
    {
        return $this->activeCouple()->exists();
    }

    public function getPartner(): ?User
    {
        $couple = $this->activeCouple()->first();
        if (!$couple) {
            return null;
        }

        return $couple->getPartnerFor($this);
    }

    public function isPremium(): bool
    {
        $subscription = $this->activeSubscription;
        return $subscription && $subscription->isPremium();
    }

    public function canSendSnap(): bool
    {
        // Premium check or daily limit
        return $this->isPremium() || $this->sentSnaps()->whereDate('created_at', today())->count() < 5;
    }

    public function updateLastActive(): void
    {
        $this->update(['last_active_at' => now()]);
    }

    public function getPushTokens(): array
    {
        return $this->notificationTokens()
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();
    }

    protected function clearModelCache(): void
    {
        Cache::forget('user_' . $this->id . '_profile');
        Cache::forget('user_' . $this->id . '_partner');
        Cache::forget('user_' . $this->id . '_subscription');
    }
}
