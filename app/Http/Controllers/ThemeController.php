<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    /**
     * Get user theme preferences.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $preferences = $user->preferences ?? $user->preferences()->create([
            'theme' => 'system',
            'color_scheme' => 'blue',
            'sidebar_collapsed' => false,
            'notifications_enabled' => true,
            'timezone' => config('app.timezone'),
        ]);
        
        return response()->json([
            'theme' => $preferences->theme,
            'color_scheme' => $preferences->color_scheme,
            'sidebar_collapsed' => $preferences->sidebar_collapsed,
            'available_themes' => [
                'light' => 'Light Mode',
                'dark' => 'Dark Mode',
                'system' => 'System Default'
            ],
            'available_colors' => [
                'blue' => 'Blue',
                'green' => 'Green',
                'purple' => 'Purple',
                'pink' => 'Pink',
                'orange' => 'Orange',
                'red' => 'Red',
                'yellow' => 'Yellow',
                'indigo' => 'Indigo',
            ]
        ]);
    }
    
    /**
     * Update theme preferences.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'theme' => 'required|in:light,dark,system',
            'color_scheme' => 'required|in:blue,green,purple,pink,orange,red,yellow,indigo',
            'sidebar_collapsed' => 'boolean',
        ]);
        
        $user = Auth::user();
        $preferences = $user->preferences ?? $user->preferences()->create([]);
        
        $preferences->update([
            'theme' => $request->theme,
            'color_scheme' => $request->color_scheme,
            'sidebar_collapsed' => $request->get('sidebar_collapsed', false),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Theme preferences updated successfully',
            'preferences' => $preferences->only(['theme', 'color_scheme', 'sidebar_collapsed'])
        ]);
    }
    
    /**
     * Reset theme to defaults.
     */
    public function reset(): JsonResponse
    {
        $user = Auth::user();
        $preferences = $user->preferences ?? $user->preferences()->create([]);
        
        $preferences->update([
            'theme' => 'system',
            'color_scheme' => 'blue',
            'sidebar_collapsed' => false,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Theme reset to defaults',
            'preferences' => $preferences->only(['theme', 'color_scheme', 'sidebar_collapsed'])
        ]);
    }
}