<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Get calendar events for a date range.
     */
    public function events(Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);
        
        $startDate = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->end);
        
        $events = Auth::user()->calendarEvents()
            ->with(['task:id,title'])
            ->betweenDates($startDate, $endDate)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start_datetime->toISOString(),
                    'end' => $event->end_datetime->toISOString(),
                    'color' => $event->color,
                    'allDay' => $event->is_all_day,
                    'type' => $event->event_type,
                    'priority' => $event->priority,
                    'status' => $event->status,
                    'description' => $event->description,
                    'location' => $event->location,
                ];
            });
        
        return response()->json($events);
    }
    
    /**
     * Store a new calendar event.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'nullable|date_format:H:i',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'event_type' => 'required|in:work,personal,meeting,appointment,other',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);
        
        $validated = $request->validated();
        
        // Compose datetime fields from separate date/time inputs
        $startDateTime = $validated['start_time'] ? 
            \Carbon\Carbon::parse($validated['start_date'] . ' ' . $validated['start_time']) : 
            \Carbon\Carbon::parse($validated['start_date']);
            
        $endDateTime = $validated['end_time'] ? 
            \Carbon\Carbon::parse($validated['end_date'] . ' ' . $validated['end_time']) : 
            \Carbon\Carbon::parse($validated['end_date']);
        
        $eventData = array_merge($validated, [
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'is_all_day' => !$validated['start_time'],
            'status' => 'scheduled'
        ]);
        
        $event = Auth::user()->calendarEvents()->create($eventData);
        
        return response()->json([
            'success' => true,
            'event' => $event,
            'message' => 'Event created successfully!'
        ], 201);
    }
    
    /**
     * Update a calendar event.
     */
    public function update(Request $request, CalendarEvent $event): JsonResponse
    {
        $this->authorize('update', $event);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'nullable|date_format:H:i',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'event_type' => 'required|in:work,personal,meeting,appointment,other',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);
        
        $validated = $request->validated();
        
        // Compose datetime fields from separate date/time inputs
        $startDateTime = $validated['start_time'] ? 
            \Carbon\Carbon::parse($validated['start_date'] . ' ' . $validated['start_time']) : 
            \Carbon\Carbon::parse($validated['start_date']);
            
        $endDateTime = $validated['end_time'] ? 
            \Carbon\Carbon::parse($validated['end_date'] . ' ' . $validated['end_time']) : 
            \Carbon\Carbon::parse($validated['end_date']);
        
        $eventData = array_merge($validated, [
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'is_all_day' => !$validated['start_time']
        ]);
        
        $event->update($eventData);
        
        return response()->json([
            'success' => true,
            'event' => $event->fresh(),
            'message' => 'Event updated successfully!'
        ]);
    }
    
    /**
     * Delete a calendar event.
     */
    public function destroy(CalendarEvent $event): JsonResponse
    {
        $this->authorize('delete', $event);
        
        $event->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully!'
        ]);
    }
    
    /**
     * Move event to new datetime (drag-and-drop support).
     */
    public function move(Request $request, CalendarEvent $event): JsonResponse
    {
        $this->authorize('update', $event);
        
        $request->validate([
            'start_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
        ]);
        
        $newStartDate = Carbon::parse($request->start_date);
        $newStartTime = $request->start_time ? 
            Carbon::parse($request->start_date . ' ' . $request->start_time) : 
            null;
        
        // Check for conflicts
        $conflicts = $this->checkEventConflicts($event, $newStartDate, $newStartTime);
        
        if ($conflicts) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot move event due to time conflicts.'
            ], 422);
        }
        
        $success = $event->moveTo($newStartDate, $newStartTime);
        
        return response()->json([
            'success' => $success,
            'event' => $event->fresh(),
            'message' => $success ? 'Event moved successfully!' : 'Failed to move event.'
        ]);
    }
    
    /**
     * Resize event duration (drag-and-drop support).
     */
    public function resize(Request $request, CalendarEvent $event): JsonResponse
    {
        $this->authorize('update', $event);
        
        $request->validate([
            'end_date' => 'required|date|after_or_equal:' . $event->start_date->toDateString(),
            'end_time' => 'nullable|date_format:H:i',
        ]);
        
        $newEndDate = Carbon::parse($request->end_date);
        $newEndTime = $request->end_time ? 
            Carbon::parse($request->end_date . ' ' . $request->end_time) : 
            null;
        
        $success = $event->resizeTo($newEndDate, $newEndTime);
        
        return response()->json([
            'success' => $success,
            'event' => $event->fresh(),
            'message' => $success ? 'Event resized successfully!' : 'Failed to resize event.'
        ]);
    }
    
    /**
     * Quick create event.
     */
    public function quickCreate(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'duration' => 'nullable|integer|min:15|max:1440',
        ]);
        
        $startDateTime = $request->start_time ? 
            Carbon::parse($request->start_date . ' ' . $request->start_time) : 
            Carbon::parse($request->start_date);
        
        $duration = $request->get('duration', 60);
        $endDateTime = $startDateTime->copy()->addMinutes($duration);
        
        $event = Auth::user()->calendarEvents()->create([
            'title' => $request->title,
            'start_date' => $startDateTime->toDateString(),
            'end_date' => $endDateTime->toDateString(),
            'start_time' => $request->start_time ? $startDateTime : null,
            'end_time' => $request->start_time ? $endDateTime : null,
            'is_all_day' => !$request->start_time,
            'event_type' => 'other',
            'priority' => 'medium',
            'status' => 'scheduled',
        ]);
        
        return response()->json([
            'success' => true,
            'event' => $event,
            'message' => 'Event created successfully!'
        ]);
    }
    
    /**
     * Check for event conflicts.
     */
    public function checkConflicts(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'nullable|date_format:H:i',
            'exclude_id' => 'nullable|integer',
        ]);
        
        $startDateTime = $request->start_time ? 
            Carbon::parse($request->start_date . ' ' . $request->start_time) : 
            Carbon::parse($request->start_date)->startOfDay();
            
        $endDateTime = $request->end_time ? 
            Carbon::parse($request->end_date . ' ' . $request->end_time) : 
            Carbon::parse($request->end_date)->endOfDay();
        
        $conflictQuery = Auth::user()->calendarEvents()
            ->whereNotIn('status', ['cancelled', 'completed']);
            
        if ($request->exclude_id) {
            $conflictQuery->where('id', '!=', $request->exclude_id);
        }
        
        $conflicts = $conflictQuery->get()->filter(function ($event) use ($startDateTime, $endDateTime) {
            $eventStart = $event->start_datetime;
            $eventEnd = $event->end_datetime;
            return $eventStart < $endDateTime && $eventEnd > $startDateTime;
        });
        
        return response()->json([
            'has_conflicts' => $conflicts->isNotEmpty(),
            'conflicts' => $conflicts->values(),
            'message' => $conflicts->isNotEmpty() ? 'Time conflicts found' : 'No conflicts'
        ]);
    }
    
    /**
     * Check for event conflicts (helper method).
     */
    private function checkEventConflicts(CalendarEvent $event, Carbon $newStartDate, ?Carbon $newStartTime): bool
    {
        $duration = $event->duration_minutes ?? 60;
        $newEndDateTime = $newStartTime ? 
            $newStartTime->copy()->addMinutes($duration) : 
            $newStartDate->copy()->endOfDay();
        $newStartDateTime = $newStartTime ?: $newStartDate->copy()->startOfDay();
        
        return Auth::user()->calendarEvents()
            ->where('id', '!=', $event->id)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->get()
            ->filter(function ($otherEvent) use ($newStartDateTime, $newEndDateTime) {
                $eventStart = $otherEvent->start_datetime;
                $eventEnd = $otherEvent->end_datetime;
                return $eventStart < $newEndDateTime && $eventEnd > $newStartDateTime;
            })
            ->isNotEmpty();
    }
}