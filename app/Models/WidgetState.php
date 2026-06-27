<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetState extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'couple_id',
        'user_id',
        'partner_id',
        'latest_mood_event_id',
        'latest_note_event_id',
        'latest_doodle_event_id',
        'latest_snap_event_id',
        'latest_distance_event_id',
        'active_countdown_id',
        'version',
        'summary',
    ];

    protected $casts = [
        'summary' => 'array',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function latestMood()
    {
        return $this->belongsTo(MoodEvent::class, 'latest_mood_event_id');
    }

    public function latestNote()
    {
        return $this->belongsTo(NoteEvent::class, 'latest_note_event_id');
    }

    public function activeCountdown()
    {
        return $this->belongsTo(Countdown::class, 'active_countdown_id');
    }

    // Methods
    public function incrementVersion(): void
    {
        $this->increment('version');
        $this->updated_at = now();
        $this->save();
    }

    public function updateLatestEvent(string $eventType, string $eventId): void
    {
        $field = 'latest_' . $eventType . '_event_id';
        $this->{$field} = $eventId;
        $this->incrementVersion();
    }
}