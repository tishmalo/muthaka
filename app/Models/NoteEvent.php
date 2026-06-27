<?php

namespace App\Models;

use App\Traits\HasAudit;
use App\Traits\HasCache;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cache;
class NoteEvent extends Model
{
    use HasFactory, HasUuid, HasAudit, HasCache;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'couple_id',
        'sender_id',
        'receiver_id',
        'content',
        'is_seen',
        'seen_at',
        'sent_at',
    ];

    protected $casts = [
        'is_seen' => 'boolean',
        'seen_at' => 'datetime',
        'sent_at' => 'datetime',
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

    public function scopeSearch($query, string $search)
    {
        return $query->where('content', 'LIKE', "%{$search}%");
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

    public function getSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'is_seen' => $this->is_seen,
        ];
    }

    public function getExcerptAttribute(): string
    {
        return strlen($this->content) > 100 
            ? substr($this->content, 0, 100) . '...' 
            : $this->content;
    }

    public function getWordCountAttribute(): int
    {
        return str_word_count($this->content);
    }

    public function getCharacterCountAttribute(): int
    {
        return strlen($this->content);
    }

    // Boot method for setting default values
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->sent_at)) {
                $model->sent_at = now();
            }
        });
    }

    protected function clearModelCache(): void
    {
        Cache::forget('note_' . $this->id);
        Cache::forget('notes_' . $this->couple_id . '_unseen');
        Cache::forget('notes_' . $this->receiver_id . '_unseen');
    }
}