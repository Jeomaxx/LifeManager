<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEventInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_event_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_cancelled',
        'custom_title',
        'custom_description',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_cancelled' => 'boolean',
    ];

    /**
     * Get the parent calendar event.
     */
    public function parentEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'parent_event_id');
    }

    /**
     * Get the title (custom or from parent).
     */
    public function getTitleAttribute(): string
    {
        return $this->custom_title ?: $this->parentEvent->title;
    }

    /**
     * Get the description (custom or from parent).
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->custom_description ?: $this->parentEvent->description;
    }

    /**
     * Scope to non-cancelled instances.
     */
    public function scopeActive($query)
    {
        return $query->where('is_cancelled', false);
    }
}
