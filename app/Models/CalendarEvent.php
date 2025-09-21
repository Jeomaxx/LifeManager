<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_all_day',
        'location',
        'event_type',
        'priority',
        'color',
        'recurrence_pattern',
        'recurrence_end_date',
        'reminder_minutes',
        'is_private',
        'status',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'time',
        'end_time' => 'time',
        'recurrence_end_date' => 'date',
        'is_all_day' => 'boolean',
        'is_private' => 'boolean',
        'reminder_minutes' => 'integer',
        'metadata' => 'json',
        'recurrence_pattern' => 'json',
    ];

    /**
     * Event types
     */
    const TYPE_TASK = 'task';
    const TYPE_MEETING = 'meeting';
    const TYPE_APPOINTMENT = 'appointment';
    const TYPE_REMINDER = 'reminder';
    const TYPE_PERSONAL = 'personal';
    const TYPE_WORK = 'work';
    const TYPE_SOCIAL = 'social';
    const TYPE_OTHER = 'other';

    /**
     * Event priorities
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Event statuses
     */
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_RESCHEDULED = 'rescheduled';

    /**
     * Get the user that owns the calendar event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get related task if this event is task-related.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get all attachments for this event.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get event instances for recurring events.
     */
    public function eventInstances(): HasMany
    {
        return $this->hasMany(CalendarEventInstance::class, 'parent_event_id');
    }

    /**
     * Get the full datetime start.
     */
    public function getStartDatetimeAttribute(): Carbon
    {
        if ($this->is_all_day || !$this->start_time) {
            return $this->start_date->startOfDay();
        }
        
        return $this->start_date->copy()->setTimeFromTimeString($this->start_time);
    }

    /**
     * Get the full datetime end.
     */
    public function getEndDatetimeAttribute(): Carbon
    {
        if ($this->is_all_day || !$this->end_time) {
            return $this->end_date->endOfDay();
        }
        
        return $this->end_date->copy()->setTimeFromTimeString($this->end_time);
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationMinutesAttribute(): int
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Check if event is happening now.
     */
    public function getIsActiveAttribute(): bool
    {
        $now = now();
        return $now->between($this->start_datetime, $this->end_datetime);
    }

    /**
     * Check if event is upcoming (within next 24 hours).
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_datetime->isFuture() && 
               $this->start_datetime->diffInHours(now()) <= 24;
    }

    /**
     * Check if event is overdue (past end time and not completed).
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->end_datetime->isPast() && 
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Get formatted time range.
     */
    public function getTimeRangeAttribute(): string
    {
        if ($this->is_all_day) {
            return 'All day';
        }
        
        return $this->start_datetime->format('H:i') . ' - ' . $this->end_datetime->format('H:i');
    }

    /**
     * Check if this event conflicts with another event.
     */
    public function conflictsWith(CalendarEvent $other): bool
    {
        return $this->start_datetime < $other->end_datetime && 
               $this->end_datetime > $other->start_datetime;
    }

    /**
     * Move event to new datetime (drag-and-drop support).
     */
    public function moveTo(Carbon $newStartDate, ?Carbon $newStartTime = null): bool
    {
        $duration = $this->duration_minutes;
        
        if ($this->is_all_day) {
            $this->start_date = $newStartDate;
            $this->end_date = $newStartDate->copy()->addMinutes($duration);
        } else {
            $startDateTime = $newStartTime ?: $newStartDate;
            $this->start_time = $startDateTime;
            $this->end_time = $startDateTime->copy()->addMinutes($duration);
            $this->start_date = $startDateTime->toDateString();
            $this->end_date = $this->end_time->toDateString();
        }
        
        return $this->save();
    }

    /**
     * Resize event duration (drag-and-drop support).
     */
    public function resizeTo(Carbon $newEndDate, ?Carbon $newEndTime = null): bool
    {
        if ($this->is_all_day) {
            $this->end_date = $newEndDate;
        } else {
            $endDateTime = $newEndTime ?: $newEndDate;
            $this->end_time = $endDateTime;
            $this->end_date = $endDateTime->toDateString();
        }
        
        return $this->save();
    }

    /**
     * Generate recurring event instances.
     */
    public function generateRecurringInstances(): array
    {
        if (!$this->recurrence_pattern) {
            return [];
        }
        
        $instances = [];
        $pattern = $this->recurrence_pattern;
        $current = $this->start_datetime->copy();
        $endLimit = $this->recurrence_end_date ?: now()->addYear();
        
        while ($current->lte($endLimit) && count($instances) < 100) {
            // Generate next occurrence based on pattern
            switch ($pattern['type']) {
                case 'daily':
                    $current->addDays($pattern['interval'] ?? 1);
                    break;
                case 'weekly':
                    if (isset($pattern['days_of_week']) && !empty($pattern['days_of_week'])) {
                        // Find next occurrence on specified days of week
                        $daysOfWeek = $pattern['days_of_week'];
                        $found = false;
                        for ($i = 1; $i <= 7; $i++) {
                            $current->addDay();
                            if (in_array($current->dayOfWeek, $daysOfWeek)) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $current->addWeeks($pattern['interval'] ?? 1);
                        }
                    } else {
                        $current->addWeeks($pattern['interval'] ?? 1);
                    }
                    break;
                case 'monthly':
                    $current->addMonths($pattern['interval'] ?? 1);
                    break;
                case 'yearly':
                    $current->addYears($pattern['interval'] ?? 1);
                    break;
                default:
                    break 2;
            }
            
            if ($current->lte($endLimit)) {
                $instances[] = [
                    'parent_event_id' => $this->id,
                    'start_date' => $current->toDateString(),
                    'end_date' => $current->copy()->addMinutes($this->duration_minutes)->toDateString(),
                    'start_time' => $this->is_all_day ? null : $current,
                    'end_time' => $this->is_all_day ? null : $current->copy()->addMinutes($this->duration_minutes),
                ];
            }
        }
        
        return $instances;
    }

    /**
     * Scope to events within date range.
     */
    public function scopeBetweenDates($query, Carbon $start, Carbon $end)
    {
        return $query->where(function($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
              ->orWhereBetween('end_date', [$start->toDateString(), $end->toDateString()])
              ->orWhere(function($q2) use ($start, $end) {
                  $q2->where('start_date', '<', $start->toDateString())
                     ->where('end_date', '>', $end->toDateString());
              });
        });
    }

    /**
     * Scope to upcoming events.
     */
    public function scopeUpcoming($query, int $hours = 24)
    {
        return $query->where('start_time', '>', now())
                    ->where('start_time', '<=', now()->addHours($hours))
                    ->orderBy('start_time');
    }

    /**
     * Scope to active events.
     */
    public function scopeActive($query)
    {
        $now = now();
        return $query->where('start_time', '<=', $now)
                    ->where('end_time', '>', $now);
    }

    /**
     * Scope to events by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('event_type', $type);
    }
}
