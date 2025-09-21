<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class NoteRequest extends FormRequest
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
        
        // Auto-generate excerpt if not provided
        if (!$this->excerpt && $this->content) {
            $plainText = strip_tags($this->content_html ?: $this->content);
            $this->merge([
                'excerpt' => \Illuminate\Support\Str::limit($plainText, 200),
            ]);
        }
        
        // Set default note type
        if (!$this->note_type) {
            $this->merge(['note_type' => 'note']);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $noteId = $this->route('note');
        $userId = Auth::id();
        
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('notes', 'title')
                    ->where('user_id', $userId)
                    ->ignore($noteId?->id ?? $noteId),
            ],
            'content' => 'required|string',
            'content_html' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('user_id', $userId)
                    ->where('type', 'general'), // Notes can use general categories
            ],
            'note_type' => 'required|in:note,journal,idea,task,research,meeting,quote,recipe,other',
            'mood' => 'nullable|in:happy,sad,excited,anxious,calm,frustrated,grateful,neutral',
            'weather' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:100',
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'is_favorite' => 'boolean',
            'is_pinned' => 'boolean',
            'is_archived' => 'boolean',
            'is_private' => 'boolean',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for your note.',
            'title.unique' => 'You already have a note with this title.',
            'content.required' => 'Please add some content to your note.',
            'note_type.required' => 'Please select a note type.',
            'note_type.in' => 'Please select a valid note type.',
            'mood.in' => 'Please select a valid mood.',
            'color.regex' => 'Color must be a valid hex color code (e.g., #FF5733).',
            'category_id.exists' => 'The selected category does not exist or does not belong to you.',
            'tags.*.max' => 'Each tag must not exceed 50 characters.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.max' => 'Each attachment must not exceed 10MB.',
            'published_at.date' => 'Published date must be a valid date.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate content length for different note types
            $minLengths = [
                'journal' => 50,
                'research' => 100,
                'meeting' => 30,
                'idea' => 10,
            ];
            
            $noteType = $this->input('note_type', 'note');
            if (isset($minLengths[$noteType])) {
                $content = strip_tags($this->input('content', ''));
                if (strlen($content) < $minLengths[$noteType]) {
                    $validator->errors()->add('content', 
                        ucfirst($noteType) . ' entries should be at least ' . 
                        $minLengths[$noteType] . ' characters long.'
                    );
                }
            }
            
            // Validate published_at is not in the future for published notes
            if ($this->filled('published_at') && $this->date('published_at')->isFuture()) {
                $validator->errors()->add('published_at', 
                    'Published date cannot be in the future.'
                );
            }
        });
    }
}
