<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistanceEvent extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'couple_id',
        'user_id',
        'latitude',
        'longitude',
        'accuracy',
        'place_name',
        'address',
        'distance_to_partner',
        'is_sharing',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
        'distance_to_partner' => 'float',
        'is_sharing' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDistanceInKmAttribute(): ?float
    {
        return $this->distance_to_partner;
    }

    public function getDistanceInMilesAttribute(): ?float
    {
        return $this->distance_to_partner ? $this->distance_to_partner * 0.621371 : null;
    }

    public function scopeSharing($query)
    {
        return $query->where('is_sharing', true);
    }
}