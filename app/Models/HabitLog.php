<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'habit_id',
        'logged_at',
        'status',
        'count',
        'notes',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'count' => 'integer',
    ];

    /**
     * Log statuses
     */
    const STATUS_COMPLETED = 'completed';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_FAILED = 'failed';

    /**
     * Get the user that owns the habit log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the habit that this log belongs to.
     */
    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }

    /**
     * Scope to today's logs.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('logged_at', now()->toDateString());
    }

    /**
     * Scope to completed logs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
