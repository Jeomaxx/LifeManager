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
        
        // Get basic stats for the dashboard
        $stats = [
            'total_tasks' => $user->tasks()->count(),
            'completed_tasks' => $user->tasks()->where('status', 'completed')->count(),
            'pending_tasks' => $user->tasks()->where('status', 'pending')->count(),
            'total_goals' => $user->goals()->count(),
            'active_habits' => $user->habits()->where('is_active', true)->count(),
            'total_notes' => $user->notes()->count(),
        ];
        
        return view('dashboard', compact('user', 'stats'));
    }
}
