<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'content',
        'content_html',
        'excerpt',
        'note_type',
        'mood',
        'weather',
        'location',
        'is_favorite',
        'is_pinned',
        'is_archived',
        'is_private',
        'color',
        'view_count',
        'word_count',
        'reading_time',
        'published_at',
        'metadata',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'is_pinned' => 'boolean',
        'is_archived' => 'boolean',
        'is_private' => 'boolean',
        'view_count' => 'integer',
        'word_count' => 'integer',
        'reading_time' => 'integer',
        'published_at' => 'datetime',
        'metadata' => 'json',
    ];

    /**
     * Note types
     */
    const TYPE_NOTE = 'note';
    const TYPE_JOURNAL = 'journal';
    const TYPE_IDEA = 'idea';
    const TYPE_TASK = 'task';
    const TYPE_RESEARCH = 'research';
    const TYPE_MEETING = 'meeting';
    const TYPE_QUOTE = 'quote';
    const TYPE_RECIPE = 'recipe';
    const TYPE_OTHER = 'other';

    /**
     * Mood types for journal entries
     */
    const MOOD_HAPPY = 'happy';
    const MOOD_SAD = 'sad';
    const MOOD_EXCITED = 'excited';
    const MOOD_ANXIOUS = 'anxious';
    const MOOD_CALM = 'calm';
    const MOOD_FRUSTRATED = 'frustrated';
    const MOOD_GRATEFUL = 'grateful';
    const MOOD_NEUTRAL = 'neutral';

    /**
     * Get the user that owns the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that contains the note.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all attachments for this note.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all tags for this note.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'note_tags')
                    ->withTimestamps();
    }

    /**
     * Get notes that are related to this note.
     */
    public function relatedNotes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_relations', 'note_id', 'related_note_id')
                    ->withPivot(['relation_type'])
                    ->withTimestamps();
    }

    /**
     * Get the formatted excerpt.
     */
    public function getFormattedExcerptAttribute(): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }
        
        // Generate excerpt from content
        $plainText = strip_tags($this->content_html ?: $this->content);
        return \Illuminate\Support\Str::limit($plainText, 200);
    }

    /**
     * Get estimated reading time in minutes.
     */
    public function getEstimatedReadingTimeAttribute(): int
    {
        if ($this->reading_time) {
            return $this->reading_time;
        }
        
        // Calculate based on average reading speed (200 words per minute)
        return max(1, ceil($this->word_count / 200));
    }

    /**
     * Get the formatted creation date.
     */
    public function getFormattedDateAttribute(): string
    {
        if ($this->published_at) {
            return $this->published_at->format('M j, Y');
        }
        
        return $this->created_at->format('M j, Y');
    }

    /**
     * Get the human-readable time since creation.
     */
    public function getTimeAgoAttribute(): string
    {
        $date = $this->published_at ?: $this->created_at;
        return $date->diffForHumans();
    }

    /**
     * Check if note was created today.
     */
    public function getIsRecentAttribute(): bool
    {
        return $this->created_at->isToday();
    }

    /**
     * Get the mood emoji.
     */
    public function getMoodEmojiAttribute(): ?string
    {
        $moodEmojis = [
            self::MOOD_HAPPY => '😊',
            self::MOOD_SAD => '😢',
            self::MOOD_EXCITED => '🤩',
            self::MOOD_ANXIOUS => '😰',
            self::MOOD_CALM => '😌',
            self::MOOD_FRUSTRATED => '😤',
            self::MOOD_GRATEFUL => '🙏',
            self::MOOD_NEUTRAL => '😐',
        ];
        
        return $moodEmojis[$this->mood] ?? null;
    }

    /**
     * Update word count and reading time.
     */
    public function updateWordCount(): void
    {
        $plainText = strip_tags($this->content_html ?: $this->content);
        $wordCount = str_word_count($plainText);
        $readingTime = max(1, ceil($wordCount / 200));
        
        $this->update([
            'word_count' => $wordCount,
            'reading_time' => $readingTime,
        ]);
    }

    /**
     * Increment view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Toggle favorite status.
     */
    public function toggleFavorite(): bool
    {
        $this->is_favorite = !$this->is_favorite;
        return $this->save();
    }

    /**
     * Generate HTML content from markdown or preserve existing HTML.
     */
    public function generateHtmlContent(): void
    {
        if (!$this->content_html && $this->content) {
            // Simple markdown to HTML conversion (in a real app, use a proper markdown parser)
            $html = $this->content;
            
            // Convert basic markdown
            $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
            $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
            $html = preg_replace('/\n\n/', '</p><p>', $html);
            $html = '<p>' . $html . '</p>';
            
            $this->content_html = $html;
            $this->save();
        }
    }

    /**
     * Scope to published notes.
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope to draft notes.
     */
    public function scopeDraft($query)
    {
        return $query->whereNull('published_at');
    }

    /**
     * Scope to favorite notes.
     */
    public function scopeFavorite($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope to pinned notes.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope to archived notes.
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope to active (non-archived) notes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope to notes by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('note_type', $type);
    }

    /**
     * Scope to search notes by content.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%')
              ->orWhere('content', 'like', '%' . $search . '%')
              ->orWhere('excerpt', 'like', '%' . $search . '%');
        });
    }


    /**
     * Scope to recent notes (last 30 days).
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to notes with specific mood.
     */
    public function scopeByMood($query, string $mood)
    {
        return $query->where('mood', $mood);
    }
}
