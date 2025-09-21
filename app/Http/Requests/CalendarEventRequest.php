<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CalendarEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => Auth::id(),
        ]);
        
        // Handle all-day events
        if ($this->boolean('is_all_day')) {
            $this->merge([
                'start_time' => null,
                'end_time' => null,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'event_type' => 'required|in:task,meeting,appointment,reminder,personal,work,social,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'reminder_minutes' => 'nullable|integer|min:0|max:10080', // Max 1 week
            'is_private' => 'boolean',
            'status' => 'required|in:scheduled,confirmed,cancelled,completed,rescheduled',
            'task_id' => 'nullable|integer|exists:tasks,id',
            'recurrence_pattern' => 'nullable|array',
            'recurrence_pattern.type' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurrence_pattern.interval' => 'nullable|integer|min:1|max:365',
            'recurrence_pattern.days_of_week' => 'nullable|array',
            'recurrence_pattern.days_of_week.*' => 'integer|between:0,6',
            'recurrence_end_date' => 'nullable|date|after:start_date',
            'metadata' => 'nullable|array',
        ];

        // Add time validation for non-all-day events
        if (!$this->boolean('is_all_day')) {
            $rules['start_time'] = 'required|date_format:H:i';
            $rules['end_time'] = 'required|date_format:H:i|after:start_time';
        }
        
        // Validate task ownership if task_id provided
        if ($this->filled('task_id')) {
            $rules['task_id'] = [
                'integer',
                Rule::exists('tasks', 'id')->where('user_id', Auth::id())
            ];
        }
        
        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for your event.',
            'start_date.required' => 'Please specify when your event starts.',
            'end_date.after_or_equal' => 'Event end date must be on or after the start date.',
            'start_time.required' => 'Please specify the start time for your event.',
            'start_time.after_or_equal' => 'Start time must be on or after the start date.',
            'end_time.required' => 'Please specify the end time for your event.',
            'end_time.after' => 'End time must be after the start time.',
            'color.regex' => 'Color must be a valid hex color code (e.g., #FF5733).',
            'reminder_minutes.max' => 'Reminder cannot be more than 1 week before the event.',
            'task_id.exists' => 'The selected task does not exist or does not belong to you.',
            'recurrence_pattern.type.in' => 'Recurrence type must be daily, weekly, monthly, or yearly.',
            'recurrence_pattern.interval.max' => 'Recurrence interval cannot exceed 365.',
            'recurrence_end_date.after' => 'Recurrence end date must be after the event start date.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check for time conflicts with existing events
            if ($this->shouldCheckConflicts()) {
                $conflicts = $this->checkTimeConflicts();
                if ($conflicts->count() > 0) {
                    $validator->errors()->add('time_conflict', 
                        'This event conflicts with existing events: ' . 
                        $conflicts->pluck('title')->implode(', ')
                    );
                }
            }
        });
    }

    /**
     * Check if we should validate time conflicts.
     */
    private function shouldCheckConflicts(): bool
    {
        return !$this->boolean('ignore_conflicts', false) && 
               $this->filled(['start_date', 'end_date']);
    }

    /**
     * Check for time conflicts with existing events.
     */
    private function checkTimeConflicts()
    {
        $userId = Auth::id();
        $eventId = $this->route('calendar') ? $this->route('calendar')->id : null;
        
        $startDateTime = $this->boolean('is_all_day') ? 
            $this->date('start_date')->startOfDay() : 
            $this->date('start_time');
            
        $endDateTime = $this->boolean('is_all_day') ? 
            $this->date('end_date')->endOfDay() : 
            $this->date('end_time');
        
        return \App\Models\CalendarEvent::where('user_id', $userId)
            ->when($eventId, fn($q) => $q->where('id', '!=', $eventId))
            ->where(function($q) use ($startDateTime, $endDateTime) {
                $q->where(function($q2) use ($startDateTime, $endDateTime) {
                    $q2->where('start_time', '<', $endDateTime)
                       ->where('end_time', '>', $startDateTime);
                })->orWhere(function($q2) use ($startDateTime, $endDateTime) {
                    // Handle all-day events
                    $q2->whereNull('start_time')
                       ->whereNull('end_time')
                       ->where('start_date', '<=', $endDateTime->toDateString())
                       ->where('end_date', '>=', $startDateTime->toDateString());
                });
            })
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->get(['id', 'title']);
    }
}
