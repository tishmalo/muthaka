<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleUser extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'couple_user';

    protected $fillable = [
        'couple_id',
        'user_id',
        'role',
        'status',
        'joined_at',
        'left_at',
        'preferences',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'preferences' => 'array',
    ];

    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPartner(): bool
    {
        return $this->role === 'partner';
    }
}