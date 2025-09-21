<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteRequest;
use App\Models\Note;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Attachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of notes.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = $user->notes()->with(['category', 'tags']);
        
        // Apply filters
        if ($request->filled('type')) {
            $query->byType($request->type);
        }
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->filled('mood')) {
            $query->byMood($request->mood);
        }
        
        if ($request->filled('tag')) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('name', $request->tag);
            });
        }
        
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        // Apply status filters
        $status = $request->get('status', 'active');
        switch ($status) {
            case 'favorites':
                $query->favorite();
                break;
            case 'pinned':
                $query->pinned();
                break;
            case 'archived':
                $query->archived();
                break;
            case 'drafts':
                $query->draft();
                break;
            case 'published':
                $query->published();
                break;
            default:
                $query->active();
        }
        
        // Sort notes
        $sortBy = $request->get('sort', 'updated_at');
        $allowedSorts = ['title', 'created_at', 'updated_at', 'published_at', 'view_count', 'word_count'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        $sortOrder = in_array($request->get('order'), ['asc', 'desc']) ? $request->get('order') : 'desc';
        
        // Pinned notes first, then sort by selected field
        $query->orderBy('is_pinned', 'desc')
              ->orderBy($sortBy, $sortOrder);
        
        $notes = $query->paginate(20);
        
        // Get filter data
        $categories = $user->categories()->where('type', 'general')->get();
        $tags = $user->tags()->withCount('notes')->orderBy('notes_count', 'desc')->take(20)->get();
        
        // Get summary stats
        $stats = [
            'total' => $user->notes()->count(),
            'published' => $user->notes()->published()->count(),
            'drafts' => $user->notes()->draft()->count(),
            'favorites' => $user->notes()->favorite()->count(),
            'archived' => $user->notes()->archived()->count(),
            'recent' => $user->notes()->recent(7)->count(),
            'total_words' => $user->notes()->sum('word_count'),
        ];
        
        return view('notes.index', compact('notes', 'categories', 'tags', 'stats', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $categories = Auth::user()->categories()->where('type', 'general')->where('is_active', true)->get();
        $tags = Auth::user()->tags()->orderBy('name')->get();
        
        // Pre-fill note type if specified
        $noteType = $request->get('type', 'note');
        
        return view('notes.create', compact('categories', 'tags', 'noteType'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NoteRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        
        // Generate HTML content if not provided
        if (empty($data['content_html']) && !empty($data['content'])) {
            $data['content_html'] = $this->convertToHtml($data['content']);
        }
        
        $note = $request->user()->notes()->create($data);
        
        // Handle tags
        if ($request->filled('tags')) {
            $this->syncTags($note, $request->input('tags'));
        }
        
        // Handle file attachments
        if ($request->hasFile('attachments')) {
            $this->handleAttachments($note, $request->file('attachments'));
        }
        
        // Update word count and reading time
        $note->updateWordCount();
        
        // Generate HTML content
        $note->generateHtmlContent();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'note' => $note->load(['category', 'tags', 'attachments']),
                'message' => 'Note created successfully!'
            ]);
        }
        
        return redirect()->route('notes.show', $note)
                        ->with('success', 'Note created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note): View
    {
        $this->authorize('view', $note);
        
        // Increment view count
        $note->incrementViewCount();
        
        $note->load(['category', 'tags', 'attachments', 'relatedNotes']);
        
        // Get related notes based on tags
        $relatedNotes = Note::where('user_id', $note->user_id)
            ->where('id', '!=', $note->id)
            ->where(function($query) use ($note) {
                if ($note->tags->count() > 0) {
                    $query->whereHas('tags', function($q) use ($note) {
                        $q->whereIn('tags.id', $note->tags->pluck('id'));
                    });
                }
                if ($note->category_id) {
                    $query->orWhere('category_id', $note->category_id);
                }
            })
            ->active()
            ->published()
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
        
        return view('notes.show', compact('note', 'relatedNotes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note): View
    {
        $this->authorize('update', $note);
        
        $categories = Auth::user()->categories()->where('type', 'general')->where('is_active', true)->get();
        $tags = Auth::user()->tags()->orderBy('name')->get();
        
        return view('notes.edit', compact('note', 'categories', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NoteRequest $request, Note $note): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $note);
        
        $data = $request->validated();
        
        // Generate HTML content if not provided
        if (empty($data['content_html']) && !empty($data['content'])) {
            $data['content_html'] = $this->convertToHtml($data['content']);
        }
        
        $note->update($data);
        
        // Handle tags
        if ($request->filled('tags')) {
            $this->syncTags($note, $request->input('tags'));
        }
        
        // Handle new file attachments
        if ($request->hasFile('attachments')) {
            $this->handleAttachments($note, $request->file('attachments'));
        }
        
        // Update word count and reading time
        $note->updateWordCount();
        
        // Generate HTML content
        $note->generateHtmlContent();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'note' => $note->fresh(['category', 'tags', 'attachments']),
                'message' => 'Note updated successfully!'
            ]);
        }
        
        return redirect()->route('notes.show', $note)
                        ->with('success', 'Note updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $note);
        
        // Delete associated attachments
        foreach ($note->attachments as $attachment) {
            Storage::delete($attachment->file_path);
            $attachment->delete();
        }
        
        $note->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully!'
            ]);
        }
        
        return redirect()->route('notes.index')
                        ->with('success', 'Note deleted successfully!');
    }
    
    /**
     * Toggle favorite status.
     */
    public function toggleFavorite(Note $note): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $note);
        
        $note->toggleFavorite();
        
        $message = $note->is_favorite ? 'Added to favorites!' : 'Removed from favorites!';
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_favorite' => $note->is_favorite,
                'message' => $message
            ]);
        }
        
        return back()->with('success', $message);
    }
    
    /**
     * Toggle pin status.
     */
    public function togglePin(Note $note): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $note);
        
        $note->update(['is_pinned' => !$note->is_pinned]);
        
        $message = $note->is_pinned ? 'Note pinned!' : 'Note unpinned!';
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_pinned' => $note->is_pinned,
                'message' => $message
            ]);
        }
        
        return back()->with('success', $message);
    }
    
    /**
     * Archive/unarchive note.
     */
    public function toggleArchive(Note $note): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $note);
        
        $note->update(['is_archived' => !$note->is_archived]);
        
        $message = $note->is_archived ? 'Note archived!' : 'Note unarchived!';
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_archived' => $note->is_archived,
                'message' => $message
            ]);
        }
        
        return back()->with('success', $message);
    }
    
    /**
     * Search notes.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);
        
        $query = $request->input('q');
        $limit = $request->input('limit', 10);
        
        $notes = Auth::user()->notes()
            ->search($query)
            ->active()
            ->published()
            ->with(['category', 'tags'])
            ->take($limit)
            ->get([
                'id', 'title', 'excerpt', 'note_type', 
                'created_at', 'updated_at', 'view_count'
            ]);
        
        return response()->json($notes);
    }
    
    /**
     * Get notes analytics.
     */
    public function analytics(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30'); // days
        
        $analytics = [
            'notes_by_type' => $user->notes()
                ->selectRaw('note_type, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays((int)$period))
                ->groupBy('note_type')
                ->pluck('count', 'note_type'),
            
            'notes_by_mood' => $user->notes()
                ->selectRaw('mood, COUNT(*) as count')
                ->whereNotNull('mood')
                ->where('created_at', '>=', now()->subDays((int)$period))
                ->groupBy('mood')
                ->pluck('count', 'mood'),
            
            'daily_notes' => $user->notes()
                ->selectRaw('date_trunc(\'day\', created_at)::date as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays((int)$period))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date'),
            
            'writing_stats' => [
                'total_words' => $user->notes()->sum('word_count'),
                'total_reading_time' => $user->notes()->sum('reading_time'),
                'average_note_length' => $user->notes()->avg('word_count'),
                'most_productive_day' => $user->notes()
                    ->selectRaw('to_char(created_at, \'FMDay\') as day, COUNT(*) as count')
                    ->groupBy('day')
                    ->orderBy('count', 'desc')
                    ->first()?->day,
            ],
        ];
        
        return response()->json($analytics);
    }
    
    /**
     * Export notes.
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'required|in:json,html,markdown',
            'include_archived' => 'boolean',
        ]);
        
        $query = Auth::user()->notes()->with(['tags', 'category']);
        
        if (!$request->boolean('include_archived')) {
            $query->active();
        }
        
        $notes = $query->get();
        
        $exportData = $notes->map(function ($note) use ($request) {
            $data = [
                'title' => $note->title,
                'content' => $note->content,
                'type' => $note->note_type,
                'tags' => $note->tags->pluck('name'),
                'category' => $note->category->name ?? null,
                'created_at' => $note->created_at->toISOString(),
                'updated_at' => $note->updated_at->toISOString(),
            ];
            
            if ($request->format === 'html') {
                $data['content_html'] = $note->content_html;
            }
            
            return $data;
        });
        
        return response()->json([
            'success' => true,
            'data' => $exportData,
            'count' => $notes->count(),
            'format' => $request->format,
            'exported_at' => now()->toISOString(),
        ]);
    }
    
    /**
     * Convert content to HTML (simple markdown-like conversion).
     */
    private function convertToHtml(string $content): string
    {
        // Simple markdown to HTML conversion
        $html = $content;
        
        // Convert basic markdown
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
        $html = preg_replace('/\n\n/', '</p><p>', $html);
        $html = '<p>' . $html . '</p>';
        
        return $html;
    }
    
    /**
     * Sync tags for a note.
     */
    private function syncTags(Note $note, array $tagNames): void
    {
        $tagIds = [];
        
        foreach ($tagNames as $tagName) {
            if (trim($tagName)) {
                $tag = Tag::firstOrCreate(
                    ['name' => trim($tagName), 'user_id' => $note->user_id],
                    ['color' => '#' . dechex(mt_rand(0, 16777215))] // Random color
                );
                $tagIds[] = $tag->id;
            }
        }
        
        $note->tags()->sync($tagIds);
    }
    
    /**
     * Handle file attachments.
     */
    private function handleAttachments(Note $note, array $files): void
    {
        foreach ($files as $file) {
            if ($file->isValid()) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('attachments/notes', $filename, 'public');
                
                $note->attachments()->create([
                    'user_id' => $note->user_id,
                    'original_name' => $file->getClientOriginalName(),
                    'filename' => $filename,
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'file_hash' => hash_file('md5', $file->getRealPath()),
                ]);
            }
        }
    }
}
