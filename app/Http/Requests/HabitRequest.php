<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HabitRequest extends FormRequest
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
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $habitId = $this->route('habit');
        $userId = Auth::id();
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('habits', 'name')
                    ->where('user_id', $userId)
                    ->ignore($habitId?->id ?? $habitId),
            ],
            'description' => 'nullable|string',
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('user_id', $userId)
                    ->where('type', 'task'), // Habits can use task categories
            ],
            'frequency_type' => 'required|in:daily,weekly,monthly,custom',
            'frequency_value' => 'required|integer|min:1|max:100',
            'target_count' => 'required|integer|min:1|max:100',
            'unit' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'is_positive' => 'boolean',
            'reminder_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a name for your habit.',
            'name.unique' => 'You already have a habit with this name.',
            'frequency_type.required' => 'Please specify how often you want to perform this habit.',
            'frequency_value.required' => 'Please specify the frequency value.',
            'frequency_value.min' => 'Frequency value must be at least 1.',
            'frequency_value.max' => 'Frequency value cannot exceed 100.',
            'target_count.required' => 'Please specify how many times you want to do this habit.',
            'target_count.min' => 'Target count must be at least 1.',
            'target_count.max' => 'Target count cannot exceed 100 per period.',
            'color.regex' => 'Color must be a valid hex color code (e.g., #FF5733).',
            'reminder_time.date_format' => 'Reminder time must be in HH:MM format.',
            'category_id.exists' => 'The selected category does not exist or does not belong to you.',
        ];
    }
}
