<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GoalRequest extends FormRequest
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
        $goalId = $this->route('goal');
        $userId = Auth::id();
        
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('goals', 'title')
                    ->where('user_id', $userId)
                    ->ignore($goalId?->id ?? $goalId),
            ],
            'description' => 'nullable|string',
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('user_id', $userId)
                    ->where('type', 'goal'),
            ],
            'target_value' => 'required|numeric|min:0.01',
            'current_progress' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:50',
            'target_date' => 'nullable|date|after:today',
            'status' => 'required|in:active,completed,paused,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'is_public' => 'boolean',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for your goal.',
            'title.unique' => 'You already have a goal with this title.',
            'target_value.required' => 'Please specify what you want to achieve (target value).',
            'target_value.min' => 'Target value must be greater than zero.',
            'unit.required' => 'Please specify the unit of measurement (e.g., "pounds", "books", "hours").',
            'target_date.after' => 'Target date must be in the future.',
            'category_id.exists' => 'The selected category does not exist or does not belong to you.',
        ];
    }
}
