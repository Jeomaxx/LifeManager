<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoryController extends Controller
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
        $query = $user->categories()->withCount(['tasks']);
        
        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        $categories = $query->orderBy('name')->paginate(20);
        
        // Get summary stats
        $stats = [
            'total' => $user->categories()->count(),
            'active' => $user->categories()->where('is_active', true)->count(),
            'inactive' => $user->categories()->where('is_active', false)->count(),
            'task_categories' => $user->categories()->where('type', 'task')->count(),
            'goal_categories' => $user->categories()->where('type', 'goal')->count(),
        ];
        
        return view('categories.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request): RedirectResponse|JsonResponse
    {
        $category = $request->user()->categories()->create($request->validated());
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => 'Category created successfully!'
            ]);
        }
        
        return redirect()->route('categories.index')
                        ->with('success', 'Category created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        $this->authorize('view', $category);
        
        $category->load(['tasks' => function($query) {
            $query->latest()->take(10);
        }]);
        
        // Get category statistics
        $stats = [
            'total_tasks' => $category->tasks()->count(),
            'completed_tasks' => $category->tasks()->where('status', 'completed')->count(),
            'pending_tasks' => $category->tasks()->where('status', 'pending')->count(),
            'overdue_tasks' => $category->tasks()->overdue()->count(),
        ];
        
        return view('categories.show', compact('category', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        $this->authorize('update', $category);
        
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $category);
        
        $category->update($request->validated());
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => 'Category updated successfully!'
            ]);
        }
        
        return redirect()->route('categories.show', $category)
                        ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $category);
        
        // Check if category has tasks
        if ($category->tasks()->count() > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category that contains tasks.'
                ], 422);
            }
            return back()->with('error', 'Cannot delete category that contains tasks. Move or delete the tasks first.');
        }
        
        $category->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!'
            ]);
        }
        
        return redirect()->route('categories.index')
                        ->with('success', 'Category deleted successfully!');
    }
    
    /**
     * Toggle category active status.
     */
    public function toggleStatus(Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        
        $category->update(['is_active' => !$category->is_active]);
        
        $status = $category->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Category {$status} successfully!");
    }
    
    /**
     * Get categories by type (AJAX endpoint).
     */
    public function getByType(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:task,goal,general',
        ]);
        
        $categories = Auth::user()
            ->categories()
            ->where('type', $request->type)
            ->where('is_active', true)
            ->select(['id', 'name', 'color'])
            ->get();
        
        return response()->json($categories);
    }
}
