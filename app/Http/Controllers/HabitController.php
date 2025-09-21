<?php

namespace App\Http\Controllers;

use App\Http\Requests\HabitRequest;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HabitController extends Controller
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
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // Sort habits
        $sortBy = $request->get('sort', 'streak_count');
        $allowedSorts = ['name', 'streak_count', 'best_streak', 'total_completions', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'streak_count';
        }
        $sortOrder = in_array($request->get('order'), ['asc', 'desc']) ? $request->get('order') : 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        $habits = $query->paginate(15);
        $categories = $user->categories()->where('type', 'task')->get(); // Habits use task categories
        
        // Get summary stats
        $stats = [
            'total' => $user->habits()->count(),
            'active' => $user->habits()->active()->count(),
            'inactive' => $user->habits()->where('is_active', false)->count(),
            'due_today' => $user->habits()->dueToday()->count(),
            'total_streaks' => $user->habits()->sum('streak_count'),
            'best_streak' => $user->habits()->max('best_streak'),
        ];
        
        return view('habits.index', compact('habits', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Auth::user()->categories()->where('type', 'task')->active()->get();
        return view('habits.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HabitRequest $request): RedirectResponse
    {
        $habit = $request->user()->habits()->create($request->validated());
        
        return redirect()->route('habits.show', $habit)
                        ->with('success', 'Habit created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Habit $habit): View
    {
        $this->authorize('view', $habit);
        
        $habit->load(['category']);
        
        // Get recent logs (last 90 days for streak visualization)
        $recentLogs = $habit->habitLogs()
            ->where('logged_at', '>=', now()->subDays(90))
            ->orderBy('logged_at', 'desc')
            ->get();
        
        // Get streak calendar data (last 3 months)
        $streakData = [];
        for ($i = 90; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dayLogs = $recentLogs->filter(function($log) use ($date) {
                return $log->logged_at->toDateString() === $date;
            });
            
            $completedCount = $dayLogs->where('status', 'completed')->sum('count');
            $status = 'none';
            
            if ($dayLogs->where('status', 'skipped')->count() > 0) {
                $status = 'skipped';
            } elseif ($completedCount >= $habit->getDailyTarget()) {
                $status = 'completed';
            } elseif ($completedCount > 0) {
                $status = 'partial';
            }
            
            $streakData[$date] = [
                'status' => $status,
                'count' => $completedCount,
            ];
        }
        
        // Get completion rates for different periods
        $completionRates = [
            '7_days' => $habit->getCompletionRate(7),
            '30_days' => $habit->getCompletionRate(30),
            '90_days' => $habit->getCompletionRate(90),
        ];
        
        return view('habits.show', compact('habit', 'recentLogs', 'streakData', 'completionRates'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Habit $habit): View
    {
        $this->authorize('update', $habit);
        
        $categories = Auth::user()->categories()->where('type', 'task')->active()->get();
        
        return view('habits.edit', compact('habit', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HabitRequest $request, Habit $habit): RedirectResponse
    {
        $this->authorize('update', $habit);
        
        $habit->update($request->validated());
        
        return redirect()->route('habits.show', $habit)
                        ->with('success', 'Habit updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Habit $habit): RedirectResponse
    {
        $this->authorize('delete', $habit);
        
        $habit->delete();
        
        return redirect()->route('habits.index')
                        ->with('success', 'Habit deleted successfully!');
    }
    
    /**
     * Log habit completion.
     */
    public function logCompletion(Request $request, Habit $habit): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $habit);
        
        $request->validate([
            'count' => 'required|integer|min:1|max:100',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $log = $habit->logCompletion($request->count, $request->notes);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'log' => $log,
                'streak' => $habit->fresh()->current_streak,
                'today_status' => $habit->fresh()->today_status,
                'message' => 'Habit completion logged! 🎉'
            ]);
        }
        
        return back()->with('success', 'Habit completion logged! 🎉');
    }
    
    /**
     * Log habit skip.
     */
    public function logSkip(Request $request, Habit $habit): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $habit);
        
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);
        
        $log = $habit->logSkip($request->reason);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'log' => $log,
                'streak' => $habit->fresh()->current_streak,
                'today_status' => $habit->fresh()->today_status,
                'message' => 'Habit skip logged.'
            ]);
        }
        
        return back()->with('info', 'Habit skip logged.');
    }
    
    /**
     * Toggle habit active status.
     */
    public function toggleStatus(Habit $habit): RedirectResponse
    {
        $this->authorize('update', $habit);
        
        $habit->update(['is_active' => !$habit->is_active]);
        
        $status = $habit->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Habit {$status} successfully!");
    }
    
    /**
     * Get habit analytics data.
     */
    public function analytics(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30'); // days
        
        $analytics = [
            'total_habits' => $user->habits()->count(),
            'active_habits' => $user->habits()->active()->count(),
            'total_completions' => $user->habits()->sum('total_completions'),
            'average_streak' => $user->habits()->avg('streak_count'),
            'best_streaks' => $user->habits()->orderBy('best_streak', 'desc')
                ->take(5)
                ->get(['name', 'best_streak']),
            'completion_trends' => $user->habitLogs()
                ->selectRaw('DATE(logged_at) as date, COUNT(*) as completions')
                ->where('status', 'completed')
                ->where('logged_at', '>=', now()->subDays((int)$period))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('completions', 'date'),
            'habits_by_frequency' => $user->habits()
                ->selectRaw('frequency_type, COUNT(*) as count')
                ->groupBy('frequency_type')
                ->pluck('count', 'frequency_type'),
        ];
        
        return response()->json($analytics);
    }
}
