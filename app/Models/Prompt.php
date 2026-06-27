<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'category',
        'emoji',
        'type',
        'is_active',
        'scheduled_date',
        'sent_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'scheduled_date' => 'date',
        'sent_at' => 'datetime',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(PromptAnswer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    public function scopeNotSent($query)
    {
        return $query->whereNull('sent_at');
    }
}

