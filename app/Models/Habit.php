<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class Habit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'frequency_type',
        'frequency_value',
        'target_count',
        'unit',
        'color',
        'is_positive',
        'reminder_time',
        'streak_count',
        'best_streak',
        'total_completions',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'frequency_value' => 'integer',
        'target_count' => 'integer',
        'is_positive' => 'boolean',
        'is_active' => 'boolean',
        'reminder_time' => 'datetime',
        'streak_count' => 'integer',
        'best_streak' => 'integer',
        'total_completions' => 'integer',
        'metadata' => 'json',
    ];

    /**
     * Habit frequency types
     */
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_CUSTOM = 'custom';

    /**
     * Get the user that owns the habit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that contains the habit.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all habit logs (completions/skips).
     */
    public function habitLogs(): HasMany
    {
        return $this->hasMany(HabitLog::class);
    }

    /**
     * Get all attachments for this habit.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get today's completion status.
     */
    public function getTodayStatusAttribute(): array
    {
        $today = now()->toDateString();
        $todayLogs = $this->habitLogs()
            ->whereDate('logged_at', $today)
            ->get();
        
        $completed = $todayLogs->where('status', 'completed')->sum('count');
        $target = $this->getDailyTarget();
        
        return [
            'completed' => $completed,
            'target' => $target,
            'is_completed' => $completed >= $target,
            'percentage' => $target > 0 ? min(100, ($completed / $target) * 100) : 0,
        ];
    }

    /**
     * Get current streak information.
     */
    public function getCurrentStreakAttribute(): array
    {
        return [
            'current' => $this->streak_count,
            'best' => $this->best_streak,
            'total_completions' => $this->total_completions,
        ];
    }

    /**
     * Get daily target based on frequency.
     */
    public function getDailyTarget(): int
    {
        switch ($this->frequency_type) {
            case self::FREQUENCY_DAILY:
                return $this->target_count;
            case self::FREQUENCY_WEEKLY:
                return (int) ceil($this->target_count / 7);
            case self::FREQUENCY_MONTHLY:
                return (int) ceil($this->target_count / 30);
            default:
                return $this->target_count;
        }
    }

    /**
     * Log habit completion for today.
     */
    public function logCompletion(int $count = 1, ?string $notes = null): HabitLog
    {
        $log = $this->habitLogs()->create([
            'user_id' => $this->user_id,
            'logged_at' => now(),
            'status' => 'completed',
            'count' => $count,
            'notes' => $notes,
        ]);
        
        $this->updateStreakCount();
        $this->increment('total_completions', $count);
        
        return $log;
    }

    /**
     * Log habit skip for today.
     */
    public function logSkip(?string $reason = null): HabitLog
    {
        $log = $this->habitLogs()->create([
            'user_id' => $this->user_id,
            'logged_at' => now(),
            'status' => 'skipped',
            'count' => 0,
            'notes' => $reason,
        ]);
        
        $this->resetStreak();
        
        return $log;
    }

    /**
     * Update streak count based on recent completions.
     */
    public function updateStreakCount(): void
    {
        $currentStreak = 0;
        $date = now()->startOfDay();
        
        // Count consecutive days of completion
        for ($i = 0; $i < 365; $i++) {
            $dateString = $date->toDateString();
            $dailyTarget = $this->getDailyTarget();
            
            $completedCount = $this->habitLogs()
                ->whereDate('logged_at', $dateString)
                ->where('status', 'completed')
                ->sum('count');
            
            if ($completedCount >= $dailyTarget) {
                $currentStreak++;
            } else {
                break;
            }
            
            $date->subDay();
        }
        
        $this->update([
            'streak_count' => $currentStreak,
            'best_streak' => max($this->best_streak, $currentStreak),
        ]);
    }

    /**
     * Reset streak count.
     */
    public function resetStreak(): void
    {
        $this->update(['streak_count' => 0]);
    }

    /**
     * Get habit completion rate for a period.
     */
    public function getCompletionRate(int $days = 30): float
    {
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();
        
        $totalExpected = $days * $this->getDailyTarget();
        $totalCompleted = $this->habitLogs()
            ->where('logged_at', '>=', $startDate)
            ->where('logged_at', '<=', $endDate)
            ->where('status', 'completed')
            ->sum('count');
        
        return $totalExpected > 0 ? min(100, ($totalCompleted / $totalExpected) * 100) : 0;
    }

    /**
     * Scope to active habits.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to habits that need attention today.
     */
    public function scopeDueToday($query)
    {
        $today = now()->toDateString();
        
        return $query->active()->whereRaw("
            (SELECT COALESCE(SUM(count), 0) 
             FROM habit_logs 
             WHERE habit_logs.habit_id = habits.id 
               AND DATE(logged_at) = ? 
               AND status = 'completed'
            ) < CASE 
                WHEN frequency_type = 'daily' THEN target_count
                WHEN frequency_type = 'weekly' THEN CEIL(target_count / 7.0)
                WHEN frequency_type = 'monthly' THEN CEIL(target_count / 30.0)
                ELSE target_count
            END
        ", [$today]);
    }
}
