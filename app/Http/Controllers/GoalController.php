<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoalRequest;
use App\Models\Goal;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GoalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = $user->goals()->with(['category']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // Sort goals
        $sortBy = $request->get('sort', 'target_date');
        $allowedSorts = ['title', 'priority', 'status', 'target_date', 'current_progress', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'target_date';
        }
        $sortOrder = in_array($request->get('order'), ['asc', 'desc']) ? $request->get('order') : 'asc';
        $query->orderBy($sortBy, $sortOrder);
        
        $goals = $query->paginate(15);
        $categories = $user->categories()->where('type', 'goal')->get();
        
        // Get summary stats
        $stats = [
            'total' => $user->goals()->count(),
            'active' => $user->goals()->where('status', 'active')->count(),
            'completed' => $user->goals()->where('status', 'completed')->count(),
            'overdue' => $user->goals()->overdue()->count(),
            'due_soon' => $user->goals()->dueSoon()->count(),
        ];
        
        return view('goals.index', compact('goals', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Auth::user()->categories()->where('type', 'goal')->active()->get();
        return view('goals.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GoalRequest $request): RedirectResponse
    {
        $goal = $request->user()->goals()->create($request->validated());
        
        return redirect()->route('goals.show', $goal)
                        ->with('success', 'Goal created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Goal $goal): View
    {
        $this->authorize('view', $goal);
        
        $goal->load(['category', 'subGoals', 'tasks' => function($query) {
            $query->latest()->take(5);
        }]);
        
        // Get progress history (last 30 days)
        $progressHistory = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            // In a real app, you'd store daily progress snapshots
            $progressHistory[$date] = $goal->current_progress;
        }
        
        return view('goals.show', compact('goal', 'progressHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Goal $goal): View
    {
        $this->authorize('update', $goal);
        
        $categories = Auth::user()->categories()->where('type', 'goal')->active()->get();
        
        return view('goals.edit', compact('goal', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GoalRequest $request, Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        
        $goal->update($request->validated());
        
        return redirect()->route('goals.show', $goal)
                        ->with('success', 'Goal updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Goal $goal): RedirectResponse
    {
        $this->authorize('delete', $goal);
        
        $goal->delete();
        
        return redirect()->route('goals.index')
                        ->with('success', 'Goal deleted successfully!');
    }
    
    /**
     * Update goal progress.
     */
    public function updateProgress(Request $request, Goal $goal): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $goal);
        
        $request->validate([
            'progress' => 'required|numeric|min:0|max:' . $goal->target_value,
        ]);
        
        $goal->updateProgress($request->progress);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'progress' => $goal->current_progress,
                'percentage' => $goal->completion_percentage,
                'is_completed' => $goal->status === Goal::STATUS_COMPLETED,
                'message' => 'Progress updated successfully!'
            ]);
        }
        
        return back()->with('success', 'Progress updated successfully!');
    }
    
    /**
     * Mark goal as completed.
     */
    public function complete(Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        
        $goal->markAsCompleted();
        
        return back()->with('success', 'Goal marked as completed! 🎉');
    }
    
    /**
     * Get goals analytics data.
     */
    public function analytics(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30'); // days
        
        $startDate = now()->subDays((int)$period);
        
        $analytics = [
            'completion_rate' => $user->goals()
                ->where('created_at', '>=', $startDate)
                ->whereIn('status', ['completed', 'active'])
                ->get()
                ->map(function($goal) {
                    return $goal->completion_percentage;
                })
                ->average(),
            
            'goals_by_status' => $user->goals()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            
            'goals_by_priority' => $user->goals()
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority'),
            
            'monthly_completions' => $user->goals()
                ->where('status', 'completed')
                ->where('updated_at', '>=', now()->subYear())
                ->selectRaw('DATE_FORMAT(updated_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month'),
        ];
        
        return response()->json($analytics);
    }
}
