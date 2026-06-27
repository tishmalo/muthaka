<?php

namespace App\Models;

use App\Enums\MoodType;
use App\Traits\HasAudit;
use App\Traits\HasCache;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cache;

class MoodEvent extends Model
{
    use HasFactory, HasUuid, HasAudit, HasCache;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'couple_id',
        'sender_id',
        'receiver_id',
        'mood_type',
        'mood_emoji',
        'mood_color',
        'notes',
        'is_seen',
        'seen_at',
        'sent_at',
    ];

    protected $casts = [
        'is_seen' => 'boolean',
        'seen_at' => 'datetime',
        'sent_at' => 'datetime',
        'mood_type' => MoodType::class,
    ];

    // Relationships
    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Scopes
    public function scopeUnseen($query)
    {
        return $query->where('is_seen', false);
    }

    public function scopeSeen($query)
    {
        return $query->where('is_seen', true);
    }

    public function scopeByMoodType($query, string $moodType)
    {
        return $query->where('mood_type', $moodType);
    }

    public function scopeBySender($query, string $senderId)
    {
        return $query->where('sender_id', $senderId);
    }

    public function scopeByReceiver($query, string $receiverId)
    {
        return $query->where('receiver_id', $receiverId);
    }

    public function scopeByCouple($query, string $coupleId)
    {
        return $query->where('couple_id', $coupleId);
    }

    public function scopeRecent($query, int $limit = 20)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Methods
    public function markAsSeen(): void
    {
        if (!$this->is_seen) {
            $this->update([
                'is_seen' => true,
                'seen_at' => now(),
            ]);
        }
    }

    public function getMoodTypeLabelAttribute(): string
    {
        return $this->mood_type->label();
    }

    public function getMoodTypeEmojiAttribute(): string
    {
        return $this->mood_type->emoji();
    }

    public function getMoodTypeColorAttribute(): string
    {
        return $this->mood_type->color();
    }

    public function getIsFromPartnerAttribute(): bool
    {
        // Will be set by the service based on current user
        return false;
    }

    public function getSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->mood_type->value,
            'emoji' => $this->mood_type->emoji(),
            'color' => $this->mood_type->color(),
            'label' => $this->mood_type->label(),
            'notes' => $this->notes,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'is_seen' => $this->is_seen,
        ];
    }

    // Boot method for setting default values
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->sent_at)) {
                $model->sent_at = now();
            }
            if (empty($model->mood_emoji) && $model->mood_type) {
                $model->mood_emoji = $model->mood_type->emoji();
            }
            if (empty($model->mood_color) && $model->mood_type) {
                $model->mood_color = $model->mood_type->color();
            }
        });
    }

    protected function clearModelCache(): void
    {
        Cache::forget('mood_' . $this->id);
        Cache::forget('moods_' . $this->couple_id . '_unseen');
        Cache::forget('moods_' . $this->receiver_id . '_unseen');
    }
}