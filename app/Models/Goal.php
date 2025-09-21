<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'target_value',
        'current_progress',
        'unit',
        'target_date',
        'status',
        'priority',
        'is_public',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'datetime',
        'target_value' => 'decimal:2',
        'current_progress' => 'decimal:2',
        'is_public' => 'boolean',
        'metadata' => 'json',
    ];

    /**
     * Goal statuses
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PAUSED = 'paused';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Goal priorities
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Get the user that owns the goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that contains the goal.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all sub-goals for this goal.
     */
    public function subGoals(): HasMany
    {
        return $this->hasMany(Goal::class, 'parent_id');
    }

    /**
     * Get the parent goal.
     */
    public function parentGoal(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'parent_id');
    }

    /**
     * Get all tasks associated with this goal.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get all attachments for this goal.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Calculate completion percentage.
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->target_value <= 0) {
            return 0;
        }
        
        return min(100, ($this->current_progress / $this->target_value) * 100);
    }

    /**
     * Check if goal is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->target_date && 
               $this->target_date->isPast() && 
               $this->status !== self::STATUS_COMPLETED;
    }

    /**
     * Get days remaining until target date.
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->target_date) {
            return null;
        }
        
        return max(0, now()->diffInDays($this->target_date, false));
    }

    /**
     * Mark goal as completed.
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'current_progress' => $this->target_value,
            'completed_at' => now(),
        ]);
    }

    /**
     * Update progress towards goal.
     */
    public function updateProgress(float $progress): bool
    {
        $newProgress = max(0, min($this->target_value, $progress));
        
        $updated = $this->update([
            'current_progress' => $newProgress,
        ]);
        
        // Auto-complete if target reached
        if ($newProgress >= $this->target_value && $this->status === self::STATUS_ACTIVE) {
            $this->markAsCompleted();
        }
        
        return $updated;
    }

    /**
     * Scope to active goals.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to completed goals.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to overdue goals.
     */
    public function scopeOverdue($query)
    {
        return $query->where('target_date', '<', now())
                    ->where('status', '!=', self::STATUS_COMPLETED);
    }

    /**
     * Scope to goals due soon (within next 7 days).
     */
    public function scopeDueSoon($query)
    {
        return $query->where('target_date', '>=', now())
                    ->where('target_date', '<=', now()->addDays(7))
                    ->where('status', self::STATUS_ACTIVE);
    }
}
