<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get overall productivity analytics.
     */
    public function productivity(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 30); // days
        $startDate = now()->subDays($period);
        
        $analytics = [
            'productivity_score' => $this->calculateProductivityScore($user, $startDate),
            'completion_rate' => $this->getCompletionRate($user, $startDate),
            'focus_time' => $this->getFocusTime($user, $startDate),
            'streak_data' => $this->getStreakData($user),
            'weekly_pattern' => $this->getWeeklyPattern($user, $startDate),
            'peak_hours' => $this->getPeakHours($user, $startDate),
        ];
        
        return response()->json($analytics);
    }
    
    /**
     * Get task-specific analytics.
     */
    public function tasks(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 30);
        $startDate = now()->subDays($period);
        
        $analytics = [
            'completion_trend' => $user->tasks()
                ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
                ->where('completed_at', '>=', $startDate)
                ->whereNotNull('completed_at')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
                
            'priority_distribution' => $user->tasks()
                ->selectRaw('priority, COUNT(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy('priority')
                ->get(),
                
            'category_performance' => $user->tasks()
                ->join('categories', 'tasks.category_id', '=', 'categories.id')
                ->selectRaw('categories.name, COUNT(*) as total, 
                           SUM(CASE WHEN tasks.status = "completed" THEN 1 ELSE 0 END) as completed')
                ->where('tasks.created_at', '>=', $startDate)
                ->groupBy('categories.id', 'categories.name')
                ->get(),
                
            'overdue_analysis' => [
                'current_overdue' => $user->tasks()->overdue()->count(),
                'overdue_trend' => $user->tasks()
                    ->selectRaw('DATE(due_date) as date, COUNT(*) as count')
                    ->where('due_date', '>=', $startDate->copy()->subDays(7))
                    ->where('due_date', '<', now())
                    ->where('status', '!=', 'completed')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ],
            
            'time_estimation_accuracy' => $this->getTimeEstimationAccuracy($user, $startDate),
        ];
        
        return response()->json($analytics);
    }
    
    /**
     * Get goals analytics.
     */
    public function goals(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 30);
        $startDate = now()->subDays($period);
        
        $analytics = [
            'completion_rate' => $user->goals()
                ->where('created_at', '>=', $startDate)
                ->get()
                ->groupBy('status')
                ->map->count(),
                
            'progress_distribution' => $user->goals()
                ->where('created_at', '>=', $startDate)
                ->selectRaw('
                    CASE 
                        WHEN progress_percentage = 0 THEN "Not Started"
                        WHEN progress_percentage < 25 THEN "Just Started"
                        WHEN progress_percentage < 50 THEN "Making Progress"
                        WHEN progress_percentage < 75 THEN "More Than Half"
                        WHEN progress_percentage < 100 THEN "Almost Done"
                        ELSE "Completed"
                    END as progress_range,
                    COUNT(*) as count
                ')
                ->groupBy('progress_range')
                ->get(),
                
            'deadline_performance' => $this->getGoalDeadlinePerformance($user, $startDate),
            
            'category_success_rate' => $user->goals()
                ->join('categories', 'goals.category_id', '=', 'categories.id')
                ->selectRaw('categories.name, COUNT(*) as total,
                           SUM(CASE WHEN goals.status = "completed" THEN 1 ELSE 0 END) as completed')
                ->where('goals.created_at', '>=', $startDate)
                ->groupBy('categories.id', 'categories.name')
                ->get(),
        ];
        
        return response()->json($analytics);
    }
    
    /**
     * Get habits analytics.
     */
    public function habits(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 30);
        $startDate = now()->subDays($period);
        
        $analytics = [
            'consistency_rates' => $user->habits()
                ->with(['habitLogs' => function($query) use ($startDate) {
                    $query->where('logged_at', '>=', $startDate);
                }])
                ->get()
                ->map(function($habit) use ($period) {
                    $expectedLogs = $period; // Assuming daily habits
                    $actualLogs = $habit->habitLogs->count();
                    return [
                        'habit' => $habit->name,
                        'consistency' => min(100, ($actualLogs / $expectedLogs) * 100),
                        'streak' => $this->calculateHabitStreak($habit),
                    ];
                }),
                
            'completion_by_day' => $user->habits()
                ->join('habit_logs', 'habits.id', '=', 'habit_logs.habit_id')
                ->selectRaw('DAYOFWEEK(logged_at) as day, COUNT(*) as count')
                ->where('logged_at', '>=', $startDate)
                ->groupBy('day')
                ->get(),
                
            'best_performing_habits' => $user->habits()
                ->withCount(['habitLogs' => function($query) use ($startDate) {
                    $query->where('logged_at', '>=', $startDate);
                }])
                ->orderBy('habit_logs_count', 'desc')
                ->take(5)
                ->get(),
        ];
        
        return response()->json($analytics);
    }
    
    /**
     * Get calendar analytics.
     */
    public function calendar(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 30);
        $startDate = now()->subDays($period);
        
        $analytics = [
            'time_distribution' => $user->calendarEvents()
                ->selectRaw('event_type, SUM(duration_minutes) as total_minutes')
                ->where('start_date', '>=', $startDate)
                ->groupBy('event_type')
                ->get(),
                
            'busy_days' => $user->calendarEvents()
                ->selectRaw('DATE(start_date) as date, COUNT(*) as event_count, 
                           SUM(duration_minutes) as total_minutes')
                ->where('start_date', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('total_minutes', 'desc')
                ->take(10)
                ->get(),
                
            'meeting_patterns' => $user->calendarEvents()
                ->selectRaw('HOUR(start_time) as hour, COUNT(*) as count')
                ->where('start_date', '>=', $startDate)
                ->whereNotNull('start_time')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
                
            'priority_time_allocation' => $user->calendarEvents()
                ->selectRaw('priority, SUM(duration_minutes) as total_minutes')
                ->where('start_date', '>=', $startDate)
                ->groupBy('priority')
                ->get(),
        ];
        
        return response()->json($analytics);
    }
    
    /**
     * Get time tracking analytics.
     */
    public function timeTracking(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 30);
        $startDate = now()->subDays($period);
        
        // This would require implementing time tracking functionality
        $analytics = [
            'total_tracked_time' => 0, // Placeholder
            'time_by_category' => [],
            'productivity_hours' => [],
            'time_estimation_vs_actual' => [],
        ];
        
        return response()->json($analytics);
    }
    
    /**
     * Get overview analytics for dashboard.
     */
    public function overview(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', 7);
        $startDate = now()->subDays($period);
        
        $analytics = [
            'summary' => [
                'tasks_completed' => $user->tasks()
                    ->where('completed_at', '>=', $startDate)
                    ->count(),
                'goals_achieved' => $user->goals()
                    ->where('status', 'completed')
                    ->where('updated_at', '>=', $startDate)
                    ->count(),
                'habits_logged' => $user->habits()
                    ->join('habit_logs', 'habits.id', '=', 'habit_logs.habit_id')
                    ->where('logged_at', '>=', $startDate)
                    ->count(),
                'events_attended' => $user->calendarEvents()
                    ->where('start_date', '>=', $startDate)
                    ->where('status', 'completed')
                    ->count(),
            ],
            
            'trends' => [
                'task_completion_trend' => $this->getTrendData($user, 'tasks', $startDate),
                'goal_progress_trend' => $this->getTrendData($user, 'goals', $startDate),
                'habit_consistency_trend' => $this->getTrendData($user, 'habits', $startDate),
            ],
            
            'performance_score' => $this->calculateOverallPerformanceScore($user, $startDate),
        ];
        
        return response()->json($analytics);
    }
    
    // Helper methods
    private function calculateProductivityScore($user, $startDate): float
    {
        $completedTasks = $user->tasks()->where('completed_at', '>=', $startDate)->count();
        $totalTasks = $user->tasks()->where('created_at', '>=', $startDate)->count();
        $completedGoals = $user->goals()->where('status', 'completed')->where('updated_at', '>=', $startDate)->count();
        
        if ($totalTasks === 0) return 0;
        
        $taskScore = ($completedTasks / $totalTasks) * 70;
        $goalScore = $completedGoals * 15;
        $consistencyScore = 15; // Placeholder for habit consistency
        
        return min(100, $taskScore + $goalScore + $consistencyScore);
    }
    
    private function getCompletionRate($user, $startDate): array
    {
        $totalTasks = $user->tasks()->where('created_at', '>=', $startDate)->count();
        $completedTasks = $user->tasks()->where('completed_at', '>=', $startDate)->count();
        
        return [
            'tasks' => $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0,
            'goals' => 0, // Placeholder
            'habits' => 0, // Placeholder
        ];
    }
    
    private function getFocusTime($user, $startDate): array
    {
        // Placeholder - would require time tracking implementation
        return [
            'total_minutes' => 0,
            'average_session' => 0,
            'longest_session' => 0,
        ];
    }
    
    private function getStreakData($user): array
    {
        // Calculate current streak for tasks
        $currentStreak = 0;
        $date = now();
        
        while ($user->tasks()->whereDate('completed_at', $date)->exists()) {
            $currentStreak++;
            $date = $date->subDay();
        }
        
        return [
            'current_streak' => $currentStreak,
            'longest_streak' => 0, // Would need to be calculated from historical data
            'streak_type' => 'daily_tasks',
        ];
    }
    
    private function getWeeklyPattern($user, $startDate): array
    {
        return $user->tasks()
            ->selectRaw('DAYOFWEEK(completed_at) as day, COUNT(*) as count')
            ->where('completed_at', '>=', $startDate)
            ->whereNotNull('completed_at')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();
    }
    
    private function getPeakHours($user, $startDate): array
    {
        return $user->tasks()
            ->selectRaw('HOUR(completed_at) as hour, COUNT(*) as count')
            ->where('completed_at', '>=', $startDate)
            ->whereNotNull('completed_at')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->take(3)
            ->pluck('count', 'hour')
            ->toArray();
    }
    
    private function getTimeEstimationAccuracy($user, $startDate): array
    {
        $tasks = $user->tasks()
            ->whereNotNull('estimated_minutes')
            ->whereNotNull('actual_minutes')
            ->where('completed_at', '>=', $startDate)
            ->get();
            
        if ($tasks->isEmpty()) {
            return ['accuracy' => 0, 'average_variance' => 0];
        }
        
        $totalVariance = $tasks->sum(function($task) {
            return abs($task->estimated_minutes - $task->actual_minutes);
        });
        
        $averageVariance = $totalVariance / $tasks->count();
        $accuracy = max(0, 100 - ($averageVariance / 60 * 100)); // Convert to percentage
        
        return [
            'accuracy' => $accuracy,
            'average_variance' => $averageVariance,
        ];
    }
    
    private function getGoalDeadlinePerformance($user, $startDate): array
    {
        $goals = $user->goals()
            ->whereNotNull('deadline')
            ->where('status', 'completed')
            ->where('completed_at', '>=', $startDate)
            ->get();
            
        $onTime = $goals->filter(function($goal) {
            return $goal->completed_at <= $goal->deadline;
        })->count();
        
        return [
            'on_time_completion' => $goals->count() > 0 ? ($onTime / $goals->count()) * 100 : 0,
            'average_delay_days' => 0, // Would need to calculate
        ];
    }
    
    private function calculateHabitStreak($habit): int
    {
        $streak = 0;
        $date = now();
        
        while ($habit->habitLogs()->whereDate('logged_at', $date)->exists()) {
            $streak++;
            $date = $date->subDay();
        }
        
        return $streak;
    }
    
    private function getTrendData($user, $type, $startDate): array
    {
        // Placeholder implementation
        return [];
    }
    
    private function calculateOverallPerformanceScore($user, $startDate): float
    {
        return $this->calculateProductivityScore($user, $startDate);
    }
}