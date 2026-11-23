<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'title' => ['required_without_all:body,is_pinned', 'filled', 'string', 'max:120'],
            'body' => ['sometimes', 'required', 'string', 'max:10000'],
            'is_pinned' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
