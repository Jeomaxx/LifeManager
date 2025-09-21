<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Get enhanced stats for the dashboard
        $stats = [
            'total_tasks' => $user->tasks()->count(),
            'completed_tasks' => $user->tasks()->where('status', 'completed')->count(),
            'pending_tasks' => $user->tasks()->where('status', 'pending')->count(),
            'in_progress_tasks' => $user->tasks()->where('status', 'in_progress')->count(),
            'overdue_tasks' => $user->tasks()->overdue()->count(),
            'total_goals' => $user->goals()->count(),
            'completed_goals' => $user->goals()->where('status', 'completed')->count(),
            'active_habits' => $user->habits()->where('is_active', true)->count(),
            'total_notes' => $user->notes()->count(),
            'total_events' => $user->calendarEvents()->count(),
            'upcoming_events' => $user->calendarEvents()->upcoming()->count(),
        ];
        
        // Get productivity analytics
        $analytics = $this->getProductivityAnalytics($user);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);
        
        // Get achievement data
        $achievements = $this->getAchievements($user);
        
        return view('dashboard', compact('user', 'stats', 'analytics', 'recentActivities', 'achievements'));
    }
    
    /**
     * Get productivity analytics for charts
     */
    private function getProductivityAnalytics($user): array
    {
        $last30Days = now()->subDays(30);
        
        return [
            'task_completion_trend' => $user->tasks()
                ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
                ->where('completed_at', '>=', $last30Days)
                ->whereNotNull('completed_at')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date'),
                
            'tasks_by_priority' => $user->tasks()
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority'),
                
            'goals_progress' => $user->goals()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
                
            'weekly_productivity' => $user->tasks()
                ->selectRaw('DAYOFWEEK(completed_at) as day, COUNT(*) as count')
                ->where('completed_at', '>=', $last30Days)
                ->whereNotNull('completed_at')
                ->groupBy('day')
                ->pluck('count', 'day'),
                
            'category_distribution' => $user->tasks()
                ->join('categories', 'tasks.category_id', '=', 'categories.id')
                ->selectRaw('categories.name, COUNT(*) as count')
                ->groupBy('categories.name')
                ->pluck('count', 'name'),
        ];
    }
    
    /**
     * Get recent activities across all modules
     */
    private function getRecentActivities($user): array
    {
        $activities = collect();
        
        // Recent tasks
        $recentTasks = $user->tasks()
            ->where('updated_at', '>=', now()->subDays(7))
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($task) {
                return [
                    'type' => 'task',
                    'action' => $task->status === 'completed' ? 'completed' : 'updated',
                    'title' => $task->title,
                    'date' => $task->updated_at,
                    'icon' => 'task',
                    'color' => $task->status_color
                ];
            });
        
        // Recent goals
        $recentGoals = $user->goals()
            ->where('updated_at', '>=', now()->subDays(7))
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get()
            ->map(function($goal) {
                return [
                    'type' => 'goal',
                    'action' => 'updated',
                    'title' => $goal->title,
                    'date' => $goal->updated_at,
                    'icon' => 'goal',
                    'color' => 'green'
                ];
            });
        
        // Recent notes
        $recentNotes = $user->notes()
            ->where('updated_at', '>=', now()->subDays(7))
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get()
            ->map(function($note) {
                return [
                    'type' => 'note',
                    'action' => 'updated',
                    'title' => $note->title,
                    'date' => $note->updated_at,
                    'icon' => 'note',
                    'color' => 'yellow'
                ];
            });
        
        return $activities
            ->concat($recentTasks)
            ->concat($recentGoals)
            ->concat($recentNotes)
            ->sortByDesc('date')
            ->take(10)
            ->values()
            ->toArray();
    }
    
    /**
     * Get user achievements and gamification data
     */
    private function getAchievements($user): array
    {
        $completedTasks = $user->tasks()->where('status', 'completed')->count();
        $completedGoals = $user->goals()->where('status', 'completed')->count();
        $totalNotes = $user->notes()->count();
        
        $achievements = [
            'task_master' => [
                'name' => 'Task Master',
                'description' => 'Complete 100 tasks',
                'progress' => min(100, $completedTasks),
                'target' => 100,
                'icon' => 'trophy',
                'completed' => $completedTasks >= 100
            ],
            'goal_crusher' => [
                'name' => 'Goal Crusher',
                'description' => 'Complete 25 goals',
                'progress' => min(25, $completedGoals),
                'target' => 25,
                'icon' => 'target',
                'completed' => $completedGoals >= 25
            ],
            'note_taker' => [
                'name' => 'Note Taker',
                'description' => 'Create 50 notes',
                'progress' => min(50, $totalNotes),
                'target' => 50,
                'icon' => 'book',
                'completed' => $totalNotes >= 50
            ]
        ];
        
        return [
            'achievements' => $achievements,
            'total_points' => $completedTasks * 10 + $completedGoals * 50 + $totalNotes * 5,
            'level' => floor(($completedTasks * 10 + $completedGoals * 50 + $totalNotes * 5) / 500) + 1
        ];
    }
}
