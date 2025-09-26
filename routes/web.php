<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
    
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Theme management
    Route::get('api/theme', [\App\Http\Controllers\ThemeController::class, 'index']);
    Route::post('api/theme', [\App\Http\Controllers\ThemeController::class, 'update']);
    Route::post('api/theme/reset', [\App\Http\Controllers\ThemeController::class, 'reset']);
    
    // Download routes - Secured with path validation and rate limiting
    Route::get('download/backup/{file}', function($file) {
        // Validate filename to prevent path traversal
        $file = basename($file);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.(json|csv|pdf)$/', $file)) {
            abort(400, 'Invalid file format');
        }
        
        // Construct and validate path
        $path = storage_path('app/temp/' . $file);
        $realPath = realpath($path);
        $tempDir = realpath(storage_path('app/temp'));
        
        // Ensure file is within temp directory and exists
        if (!$realPath || !str_starts_with($realPath, $tempDir) || !file_exists($realPath)) {
            abort(404);
        }
        
        // Verify file belongs to current user (check file prefix with user ID)
        $user = Auth::user();
        if (!str_starts_with($file, $user->id . '_')) {
            abort(403, 'Unauthorized access to file');
        }
        
        return response()->download($realPath)->deleteFileAfterSend(true);
    })->name('download.backup')->middleware('throttle:10,1');
    
    // Task management routes
    Route::resource('tasks', \App\Http\Controllers\TaskController::class);
    Route::post('tasks/{task}/complete', [\App\Http\Controllers\TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('tasks/bulk-action', [\App\Http\Controllers\TaskController::class, 'bulkAction'])->name('tasks.bulk-action');
    
    // Category management routes
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);
    Route::patch('categories/{category}/toggle-status', [\App\Http\Controllers\CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::get('api/categories/by-type', [\App\Http\Controllers\CategoryController::class, 'getByType'])->name('categories.by-type');
    
    // Goal management routes
    Route::resource('goals', \App\Http\Controllers\GoalController::class);
    Route::patch('goals/{goal}/progress', [\App\Http\Controllers\GoalController::class, 'updateProgress'])->name('goals.update-progress');
    Route::post('goals/{goal}/complete', [\App\Http\Controllers\GoalController::class, 'complete'])->name('goals.complete');
    Route::get('api/goals/analytics', [\App\Http\Controllers\GoalController::class, 'analytics'])->name('goals.analytics');
    
    // Habit management routes
    Route::resource('habits', \App\Http\Controllers\HabitController::class);
    Route::post('habits/{habit}/log-completion', [\App\Http\Controllers\HabitController::class, 'logCompletion'])->name('habits.log-completion');
    Route::post('habits/{habit}/log-skip', [\App\Http\Controllers\HabitController::class, 'logSkip'])->name('habits.log-skip');
    Route::patch('habits/{habit}/toggle-status', [\App\Http\Controllers\HabitController::class, 'toggleStatus'])->name('habits.toggle-status');
    Route::get('api/habits/analytics', [\App\Http\Controllers\HabitController::class, 'analytics'])->name('habits.analytics');
    
    // Calendar & Scheduling routes
    Route::resource('calendar', \App\Http\Controllers\CalendarController::class);
    Route::patch('calendar/{calendar}/move', [\App\Http\Controllers\CalendarController::class, 'move'])->name('calendar.move');
    Route::patch('calendar/{calendar}/resize', [\App\Http\Controllers\CalendarController::class, 'resize'])->name('calendar.resize');
    Route::get('api/calendar/events', [\App\Http\Controllers\CalendarController::class, 'getEvents'])->name('calendar.events');
    Route::post('api/calendar/quick-create', [\App\Http\Controllers\CalendarController::class, 'quickCreate'])->name('calendar.quick-create');
    Route::get('api/calendar/analytics', [\App\Http\Controllers\CalendarController::class, 'analytics'])->name('calendar.analytics');
    
    // Notes & Personal Journal routes
    Route::resource('notes', \App\Http\Controllers\NoteController::class);
    Route::patch('notes/{note}/toggle-favorite', [\App\Http\Controllers\NoteController::class, 'toggleFavorite'])->name('notes.toggle-favorite');
    Route::patch('notes/{note}/toggle-pin', [\App\Http\Controllers\NoteController::class, 'togglePin'])->name('notes.toggle-pin');
    Route::patch('notes/{note}/toggle-archive', [\App\Http\Controllers\NoteController::class, 'toggleArchive'])->name('notes.toggle-archive');
    Route::get('api/notes/search', [\App\Http\Controllers\NoteController::class, 'search'])->name('notes.search');
    Route::get('api/notes/analytics', [\App\Http\Controllers\NoteController::class, 'analytics'])->name('notes.analytics');
    Route::get('api/notes/export', [\App\Http\Controllers\NoteController::class, 'export'])->name('notes.export');
});
