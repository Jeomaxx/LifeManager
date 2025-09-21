<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HabitController extends Controller
{
    /**
     * Display a listing of habits.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $user->habits()->with(['category']);
        
        // Apply filters
        if ($request->filled('frequency_type')) {
            $query->where('frequency_type', $request->frequency_type);
        }
        
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $habits = $query->orderBy('streak_count', 'desc')->paginate(15);
        
        return response()->json($habits);
    }
    
    /**
     * Store a newly created habit.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency_type' => 'required|in:daily,weekly,monthly',
            'frequency_value' => 'nullable|integer|min:1',
            'category_id' => 'nullable|exists:categories,id',
            'target_count' => 'nullable|integer|min:1',
        ]);
        
        $habit = $request->user()->habits()->create($request->validated());
        
        return response()->json([
            'success' => true,
            'habit' => $habit->load(['category']),
            'message' => 'Habit created successfully!'
        ], 201);
    }
    
    /**
     * Display the specified habit.
     */
    public function show(Habit $habit): JsonResponse
    {
        $this->authorize('view', $habit);
        
        return response()->json($habit->load(['category']));
    }
    
    /**
     * Update the specified habit.
     */
    public function update(Request $request, Habit $habit): JsonResponse
    {
        $this->authorize('update', $habit);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency_type' => 'required|in:daily,weekly,monthly',
            'frequency_value' => 'nullable|integer|min:1',
            'category_id' => 'nullable|exists:categories,id',
            'target_count' => 'nullable|integer|min:1',
        ]);
        
        $habit->update($request->validated());
        
        return response()->json([
            'success' => true,
            'habit' => $habit->fresh(['category']),
            'message' => 'Habit updated successfully!'
        ]);
    }
    
    /**
     * Remove the specified habit.
     */
    public function destroy(Habit $habit): JsonResponse
    {
        $this->authorize('delete', $habit);
        
        $habit->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Habit deleted successfully!'
        ]);
    }
    
    /**
     * Log a habit completion.
     */
    public function logCompletion(Request $request, Habit $habit): JsonResponse
    {
        $this->authorize('update', $habit);
        
        // Check if already logged today
        $today = now()->format('Y-m-d');
        $existingLog = $habit->habitLogs()
            ->whereDate('logged_at', $today)
            ->first();
            
        if ($existingLog) {
            return response()->json([
                'success' => false,
                'message' => 'Habit already logged for today!'
            ], 422);
        }
        
        // Create habit log
        $habitLog = $habit->habitLogs()->create([
            'logged_at' => now(),
            'notes' => $request->input('notes')
        ]);
        
        // Update habit streak and stats
        $habit->updateStreakAndStats();
        
        return response()->json([
            'success' => true,
            'habit' => $habit->fresh(),
            'log' => $habitLog,
            'message' => 'Habit logged successfully!'
        ]);
    }
    
    /**
     * Get habit streak information.
     */
    public function getStreak(Habit $habit): JsonResponse
    {
        $this->authorize('view', $habit);
        
        $streakData = [
            'current_streak' => $habit->streak_count,
            'best_streak' => $habit->best_streak,
            'total_completions' => $habit->total_completions,
            'completion_rate' => $habit->calculateCompletionRate(),
            'logged_today' => $habit->isLoggedToday()
        ];
        
        return response()->json($streakData);
    }
}