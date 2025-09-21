<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
        $userId = Auth::id();
        $categoryId = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where('user_id', $userId)
                    ->where('type', $this->input('type', 'general'))
                    ->ignore($categoryId?->id ?? $categoryId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'type' => ['required', Rule::in(['task', 'goal', 'general'])],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.max' => 'Category name cannot exceed 255 characters.',
            'name.unique' => 'You already have a category with this name and type.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'color.required' => 'Please select a category color.',
            'color.regex' => 'Color must be a valid hex color code (e.g., #3B82F6).',
            'type.required' => 'Please select a category type.',
            'type.in' => 'Category type must be task, goal, or general.',
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

        // Set default values
        if ($this->is_active === null) {
            $this->merge(['is_active' => true]);
        }

        if ($this->type === null) {
            $this->merge(['type' => 'general']);
        }

        if ($this->color === null) {
            $this->merge(['color' => '#3B82F6']);
        }
    }
}
