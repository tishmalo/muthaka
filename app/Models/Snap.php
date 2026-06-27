<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Snap extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'couple_id',
        'sender_id',
        'receiver_id',
        'image_path',
        'thumbnail_path',
        'duration',
        'is_seen',
        'seen_at',
        'expires_at',
        'sent_at',
    ];

    protected $casts = [
        'is_seen' => 'boolean',
        'seen_at' => 'datetime',
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'duration' => 'integer',
    ];

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

    public function views(): HasMany
    {
        return $this->hasMany(SnapView::class);
    }

    public function markAsSeen(): void
    {
        $this->update([
            'is_seen' => true,
            'seen_at' => now(),
        ]);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function scopeUnexpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeUnseen($query)
    {
        return $query->where('is_seen', false);
    }
}