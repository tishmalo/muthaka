<?php

namespace App\Models;

use App\Traits\HasAudit;
use App\Traits\HasCache;
use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cache;
class Countdown extends Model
{
    use HasFactory, HasUuid, HasAudit, HasCache;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'couple_id',
        'user_id',
        'event_name',
        'event_date',
        'background_color',
        'icon_emoji',
        'is_active',
        'is_birthday',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_active' => 'boolean',
        'is_birthday' => 'boolean',
    ];

    // Relationships
    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

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

    public function scopeBirthdays($query)
    {
        return $query->where('is_birthday', true);
    }

    public function scopeNonBirthdays($query)
    {
        return $query->where('is_birthday', false);
    }

    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->where('event_date', '>=', now()->startOfDay())
            ->where('event_date', '<=', now()->addDays($days));
    }

    public function scopePast($query)
    {
        return $query->where('event_date', '<', now()->startOfDay());
    }

    public function scopeByCouple($query, string $coupleId)
    {
        return $query->where('couple_id', $coupleId);
    }

    // Methods
    public function getDaysRemainingAttribute(): int
    {
        $now = now()->startOfDay();
        $eventDate = $this->event_date->startOfDay();
        
        if ($eventDate->isPast()) {
            return 0;
        }
        
        return $eventDate->diffInDays($now);
    }

    public function getIsPastAttribute(): bool
    {
        return $this->event_date->isPast();
    }

    public function getIsTodayAttribute(): bool
    {
        return $this->event_date->isToday();
    }

    public function getIsThisWeekAttribute(): bool
    {
        return $this->event_date->isCurrentWeek();
    }

    public function getIsThisMonthAttribute(): bool
    {
        return $this->event_date->isCurrentMonth();
    }

    public function getDaysUntilAttribute(): int
    {
        return max(0, $this->getDaysRemainingAttribute());
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->event_date->format('F j, Y');
    }

    public function getFormattedRelativeAttribute(): string
    {
        if ($this->isPast) {
            return 'Passed';
        }
        
        $days = $this->days_remaining;
        
        if ($days === 0) {
            return 'Today! 🎉';
        }
        
        if ($days === 1) {
            return 'Tomorrow! 🎉';
        }
        
        return "{$days} days away";
    }

    public function getFormattedCountdownAttribute(): array
    {
        $days = $this->days_remaining;
        
        if ($days === 0) {
            return [
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'is_today' => true,
                'is_past' => false,
            ];
        }
        
        $now = now();
        $diff = $this->event_date->diff($now);
        
        return [
            'days' => $diff->days,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s,
            'is_today' => $this->isToday,
            'is_past' => $this->isPast,
        ];
    }

    public function getSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'event_name' => $this->event_name,
            'event_date' => $this->event_date->toIso8601String(),
            'formatted_date' => $this->formatted_date,
            'days_remaining' => $this->days_remaining,
            'is_active' => $this->is_active,
            'is_birthday' => $this->is_birthday,
            'background_color' => $this->background_color,
            'icon_emoji' => $this->icon_emoji,
            'formatted_relative' => $this->formatted_relative,
            'countdown' => $this->formatted_countdown,
        ];
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function toggleActive(): void
    {
        $this->update(['is_active' => !$this->is_active]);
    }

    // Boot method
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->background_color)) {
                $model->background_color = '#FF6B6B';
            }
            if (empty($model->icon_emoji)) {
                $model->icon_emoji = '🎉';
            }
            if ($model->is_active === null) {
                $model->is_active = true;
            }
        });
    }

    protected function clearModelCache(): void
    {
        Cache::forget('countdown_' . $this->id);
        Cache::forget('countdowns_' . $this->couple_id);
        Cache::forget('countdown_active_' . $this->couple_id);
    }
}