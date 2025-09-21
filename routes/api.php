<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\HabitController;
use App\Http\Controllers\Api\NoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API routes that require authentication  
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Dashboard Analytics
    Route::prefix('dashboard')->group(function () {
        Route::get('/analytics', [DashboardController::class, 'analytics']);
        Route::get('/recent-activities', [DashboardController::class, 'recentActivities']);
        Route::get('/achievements', [DashboardController::class, 'achievements']);
    });
    
    // Analytics endpoints
    Route::prefix('analytics')->group(function () {
        Route::get('/productivity', [AnalyticsController::class, 'productivity']);
        Route::get('/tasks', [AnalyticsController::class, 'tasks']);
        Route::get('/goals', [AnalyticsController::class, 'goals']);
        Route::get('/habits', [AnalyticsController::class, 'habits']);
        Route::get('/calendar', [AnalyticsController::class, 'calendar']);
        Route::get('/time-tracking', [AnalyticsController::class, 'timeTracking']);
        Route::get('/overview', [AnalyticsController::class, 'overview']);
    });
    
    // Export functionality
    Route::prefix('export')->group(function () {
        Route::get('/tasks', [ExportController::class, 'tasks']);
        Route::get('/goals', [ExportController::class, 'goals']);
        Route::get('/habits', [ExportController::class, 'habits']);
        Route::get('/notes', [ExportController::class, 'notes']);
        Route::get('/calendar', [ExportController::class, 'calendar']);
        Route::get('/all-data', [ExportController::class, 'allData']);
        Route::post('/backup', [ExportController::class, 'createBackup']);
    });
    
    // Enhanced Task API
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{task}', [TaskController::class, 'show']);
        Route::put('/{task}', [TaskController::class, 'update']);
        Route::delete('/{task}', [TaskController::class, 'destroy']);
        Route::post('/{task}/complete', [TaskController::class, 'complete']);
        Route::post('/{task}/duplicate', [TaskController::class, 'duplicate']);
        Route::post('/bulk-action', [TaskController::class, 'bulkAction']);
        Route::get('/{task}/time-logs', [TaskController::class, 'timeLogs']);
        Route::post('/{task}/time-logs', [TaskController::class, 'addTimeLog']);
    });
    
    // Enhanced Calendar API
    Route::prefix('calendar')->group(function () {
        Route::get('/events', [CalendarController::class, 'events']);
        Route::post('/events', [CalendarController::class, 'store']);
        Route::put('/events/{event}', [CalendarController::class, 'update']);
        Route::delete('/events/{event}', [CalendarController::class, 'destroy']);
        Route::post('/events/{event}/move', [CalendarController::class, 'move']);
        Route::post('/events/{event}/resize', [CalendarController::class, 'resize']);
        Route::post('/quick-create', [CalendarController::class, 'quickCreate']);
        Route::get('/conflicts', [CalendarController::class, 'checkConflicts']);
    });
    
    // Goals API
    Route::prefix('goals')->group(function () {
        Route::get('/', [GoalController::class, 'index']);
        Route::post('/', [GoalController::class, 'store']);
        Route::get('/{goal}', [GoalController::class, 'show']);
        Route::put('/{goal}', [GoalController::class, 'update']);
        Route::delete('/{goal}', [GoalController::class, 'destroy']);
        Route::post('/{goal}/progress', [GoalController::class, 'updateProgress']);
    });
    
    // Habits API
    Route::prefix('habits')->group(function () {
        Route::get('/', [HabitController::class, 'index']);
        Route::post('/', [HabitController::class, 'store']);
        Route::get('/{habit}', [HabitController::class, 'show']);
        Route::put('/{habit}', [HabitController::class, 'update']);
        Route::delete('/{habit}', [HabitController::class, 'destroy']);
        Route::post('/{habit}/log', [HabitController::class, 'logCompletion']);
        Route::get('/{habit}/streak', [HabitController::class, 'getStreak']);
    });
    
    // Notes API
    Route::prefix('notes')->group(function () {
        Route::get('/', [NoteController::class, 'index']);
        Route::post('/', [NoteController::class, 'store']);
        Route::get('/{note}', [NoteController::class, 'show']);
        Route::put('/{note}', [NoteController::class, 'update']);
        Route::delete('/{note}', [NoteController::class, 'destroy']);
        Route::post('/{note}/favorite', [NoteController::class, 'toggleFavorite']);
        Route::get('/search', [NoteController::class, 'search']);
    });
});