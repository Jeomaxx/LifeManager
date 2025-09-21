<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalendarEventRequest;
use App\Models\CalendarEvent;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of calendar events.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $view = $request->get('view', 'month'); // month, week, day, agenda
        
        // Get date range based on view
        $date = $request->filled('date') ? Carbon::parse($request->date) : now();
        
        switch ($view) {
            case 'week':
                $startDate = $date->copy()->startOfWeek();
                $endDate = $date->copy()->endOfWeek();
                break;
            case 'day':
                $startDate = $date->copy()->startOfDay();
                $endDate = $date->copy()->endOfDay();
                break;
            case 'agenda':
                $startDate = $date->copy()->startOfDay();
                $endDate = $date->copy()->addDays(30)->endOfDay();
                break;
            default: // month
                $startDate = $date->copy()->startOfMonth()->startOfWeek();
                $endDate = $date->copy()->endOfMonth()->endOfWeek();
        }
        
        // Get base events
        $baseEvents = $user->calendarEvents()
            ->with(['task'])
            ->betweenDates($startDate, $endDate)
            ->get();
        
        // Get recurring instances within date range
        $instances = \App\Models\CalendarEventInstance::whereHas('parentEvent', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('start_date', '>=', $startDate->toDateString())
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('is_cancelled', false)
            ->with(['parentEvent.task'])
            ->get();
        
        // Combine base events and instances
        $allEvents = collect();
        
        // Add base events
        foreach ($baseEvents as $event) {
            $allEvents->push($event);
        }
        
        // Add instances as event-like objects
        foreach ($instances as $instance) {
            $event = $instance->parentEvent;
            $instanceEvent = new \stdClass();
            $instanceEvent->id = 'instance-' . $instance->id;
            $instanceEvent->title = $instance->title;
            $instanceEvent->description = $instance->description;
            $instanceEvent->start_date = $instance->start_date;
            $instanceEvent->end_date = $instance->end_date;
            $instanceEvent->start_time = $instance->start_time;
            $instanceEvent->end_time = $instance->end_time;
            $instanceEvent->is_all_day = !$instance->start_time;
            $instanceEvent->event_type = $event->event_type;
            $instanceEvent->priority = $event->priority;
            $instanceEvent->color = $event->color;
            $instanceEvent->status = $event->status;
            $instanceEvent->location = $event->location;
            $instanceEvent->task = $event->task;
            $allEvents->push($instanceEvent);
        }
        
        $query = $allEvents->filter(function($event) use ($request) {
        
            // Apply filters
            if ($request->filled('type')) {
                return $event->event_type === $request->type;
            }
            
            if ($request->filled('priority')) {
                return $event->priority === $request->priority;
            }
            
            if ($request->filled('status')) {
                return $event->status === $request->status;
            }
            
            return true;
        });
        
        $events = $query->sortBy([['start_date', 'asc'], ['start_time', 'asc']]);
        
        // Get summary stats
        $stats = [
            'total_events' => $user->calendarEvents()->count(),
            'upcoming_events' => $user->calendarEvents()->upcoming()->count(),
            'active_events' => $user->calendarEvents()->active()->count(),
            'overdue_events' => $user->calendarEvents()
                ->where('end_time', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
        ];
        
        return view('calendar.index', compact('events', 'view', 'date', 'startDate', 'endDate', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $tasks = Auth::user()->tasks()->where('status', '!=', 'completed')->get();
        
        // Pre-fill date/time if provided
        $defaultDate = $request->get('date', now()->toDateString());
        $defaultTime = $request->get('time', now()->format('H:i'));
        
        return view('calendar.create', compact('tasks', 'defaultDate', 'defaultTime'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CalendarEventRequest $request): RedirectResponse|JsonResponse
    {
        $event = $request->user()->calendarEvents()->create($request->validated());
        
        // Generate recurring instances if needed
        if ($event->recurrence_pattern) {
            $instances = $event->generateRecurringInstances();
            foreach ($instances as $instance) {
                $event->eventInstances()->create($instance);
            }
        }
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'event' => $event->load(['task']),
                'message' => 'Event created successfully!'
            ]);
        }
        
        return redirect()->route('calendar.show', $event)
                        ->with('success', 'Calendar event created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(CalendarEvent $calendar): View
    {
        $this->authorize('view', $calendar);
        
        $calendar->load(['task', 'eventInstances']);
        
        // Get nearby events (same day)
        $nearbyEvents = Auth::user()->calendarEvents()
            ->where('id', '!=', $calendar->id)
            ->whereDate('start_date', $calendar->start_date)
            ->orderBy('start_time')
            ->get();
        
        return view('calendar.show', compact('calendar', 'nearbyEvents'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CalendarEvent $calendar): View
    {
        $this->authorize('update', $calendar);
        
        $tasks = Auth::user()->tasks()->where('status', '!=', 'completed')->get();
        
        return view('calendar.edit', compact('calendar', 'tasks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CalendarEventRequest $request, CalendarEvent $calendar): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $calendar);
        
        $calendar->update($request->validated());
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'event' => $calendar->fresh(['task']),
                'message' => 'Event updated successfully!'
            ]);
        }
        
        return redirect()->route('calendar.show', $calendar)
                        ->with('success', 'Calendar event updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalendarEvent $calendar): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $calendar);
        
        $calendar->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully!'
            ]);
        }
        
        return redirect()->route('calendar.index')
                        ->with('success', 'Calendar event deleted successfully!');
    }
    
    /**
     * Move event to new datetime (drag-and-drop support).
     */
    public function move(Request $request, CalendarEvent $calendar): JsonResponse
    {
        $this->authorize('update', $calendar);
        
        $request->validate([
            'start_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
        ]);
        
        $newStartDate = Carbon::parse($request->start_date);
        $newStartTime = $request->start_time ? 
            Carbon::parse($request->start_date . ' ' . $request->start_time) : 
            null;
        
        // Check for conflicts before moving
        $duration = $calendar->duration_minutes;
        $newEndDateTime = $newStartTime ? $newStartTime->copy()->addMinutes($duration) : $newStartDate->copy()->endOfDay();
        $newStartDateTime = $newStartTime ?: $newStartDate->copy()->startOfDay();
        
        // Check conflicts by building proper datetime comparisons
        $conflicts = CalendarEvent::where('user_id', Auth::id())
            ->where('id', '!=', $calendar->id)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->get()
            ->filter(function ($event) use ($newStartDateTime, $newEndDateTime) {
                $eventStart = $event->start_datetime;
                $eventEnd = $event->end_datetime;
                return $eventStart < $newEndDateTime && $eventEnd > $newStartDateTime;
            })
            ->count() > 0;
        
        // Also check conflicts with recurring instances
        $instanceConflicts = \App\Models\CalendarEventInstance::whereHas('parentEvent', function($q) {
                $q->where('user_id', Auth::id())
                  ->whereNotIn('status', ['cancelled', 'completed']);
            })
            ->where('is_cancelled', false)
            ->get()
            ->filter(function ($instance) use ($newStartDateTime, $newEndDateTime) {
                $instanceStart = $instance->start_time ?: $instance->start_date->startOfDay();
                $instanceEnd = $instance->end_time ?: $instance->end_date->endOfDay();
                return $instanceStart < $newEndDateTime && $instanceEnd > $newStartDateTime;
            })
            ->count() > 0;
        
        if ($conflicts || $instanceConflicts) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot move event due to time conflicts with existing events.'
            ], 422);
        }
        
        $success = $calendar->moveTo($newStartDate, $newStartTime);
        
        return response()->json([
            'success' => $success,
            'event' => $calendar->fresh(),
            'message' => $success ? 'Event moved successfully!' : 'Failed to move event.'
        ]);
    }
    
    /**
     * Resize event duration (drag-and-drop support).
     */
    public function resize(Request $request, CalendarEvent $calendar): JsonResponse
    {
        $this->authorize('update', $calendar);
        
        $request->validate([
            'end_date' => 'required|date|after_or_equal:' . $calendar->start_date->toDateString(),
            'end_time' => 'nullable|date_format:H:i',
        ]);
        
        $newEndDate = Carbon::parse($request->end_date);
        $newEndTime = $request->end_time ? 
            Carbon::parse($request->end_date . ' ' . $request->end_time) : 
            null;
        
        // Basic validation: end time must be after start time
        $startDateTime = $calendar->start_datetime;
        $newEndDateTime = $newEndTime ?: $newEndDate->endOfDay();
        
        if ($newEndDateTime->lte($startDateTime)) {
            return response()->json([
                'success' => false,
                'message' => 'End time must be after start time.'
            ], 422);
        }
        
        $success = $calendar->resizeTo($newEndDate, $newEndTime);
        
        return response()->json([
            'success' => $success,
            'event' => $calendar->fresh(),
            'message' => $success ? 'Event resized successfully!' : 'Failed to resize event.'
        ]);
    }
    
    /**
     * Get events for a specific date range (AJAX endpoint).
     */
    public function getEvents(Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);
        
        $startDate = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->end);
        
        $baseEvents = Auth::user()->calendarEvents()
            ->with(['task:id,title'])
            ->betweenDates($startDate, $endDate)
            ->get();
        
        // Include recurring instances
        $instances = \App\Models\CalendarEventInstance::whereHas('parentEvent', function($q) {
                $q->where('user_id', Auth::id());
            })
            ->where('start_date', '>=', $startDate->toDateString())
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('is_cancelled', false)
            ->with(['parentEvent.task:id,title'])
            ->get();
        
        $events = $baseEvents->concat($instances->map(function($instance) {
            $event = $instance->parentEvent;
            return (object) [
                'id' => 'instance-' . $instance->id,
                'title' => $instance->title,
                'start_datetime' => $instance->start_time ?: $instance->start_date->startOfDay(),
                'end_datetime' => $instance->end_time ?: $instance->end_date->endOfDay(),
                'color' => $event->color,
                'is_all_day' => !$instance->start_time,
                'event_type' => $event->event_type,
                'priority' => $event->priority,
                'status' => $event->status,
                'description' => $instance->description,
                'location' => $event->location,
            ];
        }))->map(function ($event) {
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
     * Get calendar analytics.
     */
    public function analytics(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30'); // days
        
        $analytics = [
            'events_by_type' => $user->calendarEvents()
                ->selectRaw('event_type, COUNT(*) as count')
                ->where('start_date', '>=', now()->subDays((int)$period))
                ->groupBy('event_type')
                ->pluck('count', 'event_type'),
            
            'events_by_priority' => $user->calendarEvents()
                ->selectRaw('priority, COUNT(*) as count')
                ->where('start_date', '>=', now()->subDays((int)$period))
                ->groupBy('priority')
                ->pluck('count', 'priority'),
            
            'completion_rate' => $user->calendarEvents()
                ->where('start_date', '>=', now()->subDays((int)$period))
                ->where('end_date', '<', now())
                ->get()
                ->groupBy('status')
                ->map->count(),
            
            'daily_events' => $user->calendarEvents()
                ->selectRaw('DATE(start_date) as date, COUNT(*) as count')
                ->where('start_date', '>=', now()->subDays((int)$period))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date'),
        ];
        
        return response()->json($analytics);
    }
    
    /**
     * Quick create event (AJAX endpoint).
     */
    public function quickCreate(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'duration' => 'nullable|integer|min:15|max:1440', // 15 minutes to 24 hours
        ]);
        
        $startDateTime = $request->start_time ? 
            Carbon::parse($request->start_date . ' ' . $request->start_time) : 
            Carbon::parse($request->start_date);
        
        $duration = $request->get('duration', 60); // default 1 hour
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
}
