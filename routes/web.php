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
});
