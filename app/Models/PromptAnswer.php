<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromptAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'prompt_id',
        'couple_id',
        'user_id',
        'answer',
        'reaction',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'reaction' => 'integer',
    ];

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}