<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get dashboard analytics.
     */
    public function analytics(): JsonResponse
    {
        $user = Auth::user();
        
        $analytics = [
            'task_completion_trend' => $user->tasks()
                ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
                ->where('completed_at', '>=', now()->subDays(30))
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
        ];
        
        return response()->json($analytics);
    }
    
    /**
     * Get recent activities.
     */
    public function recentActivities(): JsonResponse
    {
        $user = Auth::user();
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
                    'color' => 'blue'
                ];
            });
        
        return response()->json($activities->concat($recentTasks)->take(10));
    }
    
    /**
     * Get achievements.
     */
    public function achievements(): JsonResponse
    {
        $user = Auth::user();
        $completedTasks = $user->tasks()->where('status', 'completed')->count();
        
        $achievements = [
            'task_master' => [
                'name' => 'Task Master',
                'description' => 'Complete 100 tasks',
                'progress' => min(100, $completedTasks),
                'target' => 100,
                'completed' => $completedTasks >= 100
            ]
        ];
        
        return response()->json([
            'achievements' => $achievements,
            'total_points' => $completedTasks * 10,
            'level' => floor($completedTasks / 10) + 1
        ]);
    }
}