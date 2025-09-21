<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => [
                'nullable', 
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    if ($value && !Auth::user()->categories()->where('id', $value)->exists()) {
                        $fail('The selected category does not belong to you.');
                    }
                },
            ],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'due_date' => ['nullable', 'date', 'after:now'],
            'is_recurring' => ['boolean'],
            'recurrence_pattern' => ['nullable', 'array'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'], // max 1 week
            'actual_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
        ];

        // Additional validation for recurring tasks
        if ($this->boolean('is_recurring')) {
            $rules['recurrence_pattern'] = ['required', 'array'];
            $rules['recurrence_pattern.type'] = ['required', Rule::in(['daily', 'weekly', 'monthly'])];
            $rules['recurrence_pattern.interval'] = ['required', 'integer', 'min:1', 'max:365'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'title.max' => 'Task title cannot exceed 255 characters.',
            'category_id.exists' => 'The selected category does not exist.',
            'priority.required' => 'Please select a task priority.',
            'priority.in' => 'Priority must be low, medium, high, or urgent.',
            'status.required' => 'Please select a task status.',
            'status.in' => 'Status must be pending, in progress, completed, or cancelled.',
            'due_date.after' => 'Due date must be in the future.',
            'estimated_minutes.min' => 'Estimated time must be at least 1 minute.',
            'estimated_minutes.max' => 'Estimated time cannot exceed 1 week (10,080 minutes).',
            'recurrence_pattern.required' => 'Recurrence pattern is required for recurring tasks.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set user_id from authenticated user
        $this->merge([
            'user_id' => Auth::id(),
        ]);

        // Convert empty strings to null for optional fields
        if ($this->category_id === '') {
            $this->merge(['category_id' => null]);
        }
        
        if ($this->due_date === '') {
            $this->merge(['due_date' => null]);
        }
    }
}
