<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
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
        $query = $user->tasks()->with(['category']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // Sort tasks (whitelist allowed columns)
        $sortBy = $request->get('sort', 'due_date');
        $allowedSorts = ['title', 'priority', 'status', 'due_date', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'due_date';
        }
        $sortOrder = in_array($request->get('order'), ['asc', 'desc']) ? $request->get('order') : 'asc';
        $query->orderBy($sortBy, $sortOrder);
        
        $tasks = $query->paginate(15);
        $categories = $user->categories()->where('type', 'task')->get();
        
        // Get summary stats
        $stats = [
            'total' => $user->tasks()->count(),
            'pending' => $user->tasks()->where('status', 'pending')->count(),
            'in_progress' => $user->tasks()->where('status', 'in_progress')->count(),
            'completed' => $user->tasks()->where('status', 'completed')->count(),
            'overdue' => $user->tasks()->overdue()->count(),
        ];
        
        return view('tasks.index', compact('tasks', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Auth::user()->categories()->where('type', 'task')->active()->get();
        return view('tasks.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request): RedirectResponse
    {
        $task = $request->user()->tasks()->create($request->validated());
        
        return redirect()->route('tasks.show', $task)
                        ->with('success', 'Task created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): View
    {
        $this->authorize('view', $task);
        
        $task->load(['category', 'attachments', 'childTasks']);
        
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task): View
    {
        $this->authorize('update', $task);
        
        $categories = Auth::user()->categories()->where('type', 'task')->active()->get();
        
        return view('tasks.edit', compact('task', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);
        
        $task->update($request->validated());
        
        return redirect()->route('tasks.show', $task)
                        ->with('success', 'Task updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);
        
        $task->delete();
        
        return redirect()->route('tasks.index')
                        ->with('success', 'Task deleted successfully!');
    }
    
    /**
     * Mark task as completed.
     */
    public function complete(Task $task): RedirectResponse
    {
        $this->authorize('update', $task);
        
        $task->markAsCompleted();
        
        return back()->with('success', 'Task marked as completed!');
    }
    
    /**
     * Bulk actions for tasks.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validationRules = [
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'action' => 'required|in:complete,delete,update_status,update_priority',
        ];
        
        // Add specific validation for value field based on action
        if ($request->action === 'update_status') {
            $validationRules['value'] = 'required|in:pending,in_progress,completed,cancelled';
        } elseif ($request->action === 'update_priority') {
            $validationRules['value'] = 'required|in:low,medium,high,urgent';
        }
        
        $request->validate($validationRules);
        
        $tasks = Auth::user()->tasks()->whereIn('id', $request->task_ids);
        
        switch ($request->action) {
            case 'complete':
                $tasks->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                $message = 'Tasks marked as completed!';
                break;
                
            case 'delete':
                $tasks->delete();
                $message = 'Tasks deleted successfully!';
                break;
                
            case 'update_status':
                $tasks->update(['status' => $request->value]);
                $message = 'Tasks status updated!';
                break;
                
            case 'update_priority':
                $tasks->update(['priority' => $request->value]);
                $message = 'Tasks priority updated!';
                break;
        }
        
        return back()->with('success', $message);
    }
}
