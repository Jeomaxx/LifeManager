<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    /**
     * Display a listing of notes.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $user->notes()->with(['attachments']);
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('is_favorite')) {
            $query->where('is_favorite', $request->boolean('is_favorite'));
        }
        
        if ($request->filled('is_pinned')) {
            $query->where('is_pinned', $request->boolean('is_pinned'));
        }
        
        if ($request->filled('is_archived')) {
            $query->where('is_archived', $request->boolean('is_archived'));
        } else {
            // By default, don't show archived notes
            $query->where('is_archived', false);
        }
        
        $notes = $query->orderBy('is_pinned', 'desc')
                      ->orderBy('updated_at', 'desc')
                      ->paginate(15);
        
        return response()->json($notes);
    }
    
    /**
     * Store a newly created note.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'color' => 'nullable|string|max:7', // hex color
        ]);
        
        $note = $request->user()->notes()->create($request->validated());
        
        return response()->json([
            'success' => true,
            'note' => $note->load(['attachments']),
            'message' => 'Note created successfully!'
        ], 201);
    }
    
    /**
     * Display the specified note.
     */
    public function show(Note $note): JsonResponse
    {
        $this->authorize('view', $note);
        
        return response()->json($note->load(['attachments']));
    }
    
    /**
     * Update the specified note.
     */
    public function update(Request $request, Note $note): JsonResponse
    {
        $this->authorize('update', $note);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);
        
        $note->update($request->validated());
        
        return response()->json([
            'success' => true,
            'note' => $note->fresh(['attachments']),
            'message' => 'Note updated successfully!'
        ]);
    }
    
    /**
     * Remove the specified note.
     */
    public function destroy(Note $note): JsonResponse
    {
        $this->authorize('delete', $note);
        
        $note->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully!'
        ]);
    }
    
    /**
     * Toggle note favorite status.
     */
    public function toggleFavorite(Note $note): JsonResponse
    {
        $this->authorize('update', $note);
        
        $note->update(['is_favorite' => !$note->is_favorite]);
        
        return response()->json([
            'success' => true,
            'note' => $note->fresh(),
            'message' => $note->is_favorite ? 'Note added to favorites!' : 'Note removed from favorites!'
        ]);
    }
    
    /**
     * Search notes.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);
        
        $user = Auth::user();
        $searchQuery = $request->query;
        $limit = $request->get('limit', 10);
        
        $notes = $user->notes()
            ->where('is_archived', false)
            ->where(function($q) use ($searchQuery) {
                $q->where('title', 'like', '%' . $searchQuery . '%')
                  ->orWhere('content', 'like', '%' . $searchQuery . '%');
            })
            ->orderBy('is_pinned', 'desc')
            ->orderBy('updated_at', 'desc')
            ->take($limit)
            ->get(['id', 'title', 'content', 'is_favorite', 'is_pinned', 'color', 'updated_at']);
        
        return response()->json([
            'success' => true,
            'notes' => $notes,
            'query' => $searchQuery,
            'count' => $notes->count()
        ]);
    }
}