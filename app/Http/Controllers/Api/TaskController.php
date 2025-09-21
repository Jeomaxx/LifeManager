<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks.
     */
    public function index(Request $request): JsonResponse
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
        
        $tasks = $query->orderBy('due_date')->paginate(15);
        
        return response()->json($tasks);
    }
    
    /**
     * Store a newly created task.
     */
    public function store(TaskRequest $request): JsonResponse
    {
        $task = $request->user()->tasks()->create($request->validated());
        
        return response()->json([
            'success' => true,
            'task' => $task->load(['category']),
            'message' => 'Task created successfully!'
        ], 201);
    }
    
    /**
     * Display the specified task.
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        
        return response()->json($task->load(['category', 'attachments']));
    }
    
    /**
     * Update the specified task.
     */
    public function update(TaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        
        $task->update($request->validated());
        
        return response()->json([
            'success' => true,
            'task' => $task->fresh(['category']),
            'message' => 'Task updated successfully!'
        ]);
    }
    
    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        
        $task->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully!'
        ]);
    }
    
    /**
     * Mark task as completed.
     */
    public function complete(Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        
        $task->markAsCompleted();
        
        return response()->json([
            'success' => true,
            'task' => $task->fresh(),
            'message' => 'Task marked as completed!'
        ]);
    }
    
    /**
     * Duplicate a task.
     */
    public function duplicate(Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        
        $newTask = $task->replicate();
        $newTask->title = $task->title . ' (Copy)';
        $newTask->status = 'pending';
        $newTask->completed_at = null;
        $newTask->save();
        
        return response()->json([
            'success' => true,
            'task' => $newTask->load(['category']),
            'message' => 'Task duplicated successfully!'
        ]);
    }
    
    /**
     * Bulk actions for tasks.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'action' => 'required|in:complete,delete,update_status,update_priority',
            'value' => 'nullable|string'
        ]);
        
        $tasks = Auth::user()->tasks()->whereIn('id', $request->task_ids);
        
        switch ($request->action) {
            case 'complete':
                $tasks->update(['status' => 'completed', 'completed_at' => now()]);
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
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}