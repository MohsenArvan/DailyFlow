<?php

namespace App\Http\Requests\Project;

use App\Enums\ProjectNoteReviewStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'title' => ['required_without_all:body,is_pinned,review_status', 'filled', 'string', 'max:120'],
            'body' => ['sometimes', 'required', 'string', 'max:10000'],
            'review_status' => ['sometimes', 'required', Rule::enum(ProjectNoteReviewStatus::class)],
            'is_pinned' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
