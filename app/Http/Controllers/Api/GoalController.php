<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    /**
     * Display a listing of goals.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $user->goals()->with(['category']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $goals = $query->orderBy('deadline')->paginate(15);
        
        return response()->json($goals);
    }
    
    /**
     * Store a newly created goal.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date|after:today',
            'category_id' => 'nullable|exists:categories,id',
            'target_value' => 'nullable|numeric|min:0',
            'target_unit' => 'nullable|string|max:50',
        ]);
        
        $goal = $request->user()->goals()->create($request->validated());
        
        return response()->json([
            'success' => true,
            'goal' => $goal->load(['category']),
            'message' => 'Goal created successfully!'
        ], 201);
    }
    
    /**
     * Display the specified goal.
     */
    public function show(Goal $goal): JsonResponse
    {
        $this->authorize('view', $goal);
        
        return response()->json($goal->load(['category']));
    }
    
    /**
     * Update the specified goal.
     */
    public function update(Request $request, Goal $goal): JsonResponse
    {
        $this->authorize('update', $goal);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
            'target_value' => 'nullable|numeric|min:0',
            'target_unit' => 'nullable|string|max:50',
        ]);
        
        $goal->update($request->validated());
        
        return response()->json([
            'success' => true,
            'goal' => $goal->fresh(['category']),
            'message' => 'Goal updated successfully!'
        ]);
    }
    
    /**
     * Remove the specified goal.
     */
    public function destroy(Goal $goal): JsonResponse
    {
        $this->authorize('delete', $goal);
        
        $goal->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Goal deleted successfully!'
        ]);
    }
    
    /**
     * Update goal progress.
     */
    public function updateProgress(Request $request, Goal $goal): JsonResponse
    {
        $this->authorize('update', $goal);
        
        $request->validate([
            'current_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        
        $goal->update([
            'current_value' => $request->current_value,
            'progress_percentage' => $goal->target_value > 0 ? 
                min(100, ($request->current_value / $goal->target_value) * 100) : 0,
        ]);
        
        // Log progress if we have a progress tracking system
        // $goal->progressLogs()->create($request->validated());
        
        return response()->json([
            'success' => true,
            'goal' => $goal->fresh(),
            'message' => 'Progress updated successfully!'
        ]);
    }
}