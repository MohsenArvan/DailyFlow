<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'array', 'max:10'],
            'tags.*' => ['required', 'string', 'max:32', 'distinct'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'in:none,low,medium,high,urgent'],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
            'due_time' => ['nullable'],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->where('user_id', $this->user()->id)->withoutTrashed()],
            'recurrence_rule' => ['nullable', 'array:frequency,interval,by_weekday,end_date'],
            'recurrence_rule.frequency' => ['required_with:recurrence_rule', 'in:daily,weekdays,weekly,monthly,yearly,custom'],
            'recurrence_rule.interval' => ['sometimes', 'integer', 'between:1,365'],
            'recurrence_rule.by_weekday' => ['required_if:recurrence_rule.frequency,custom', 'array', 'min:1', 'max:7'],
            'recurrence_rule.by_weekday.*' => ['integer', 'between:0,6', 'distinct'],
            'recurrence_rule.end_date' => ['nullable', 'date_format:Y-m-d'],
            'reminder_at' => ['nullable', 'date_format:Y-m-d\\TH:i:sP'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان تسک الزامی است.',
            'title.max' => 'عنوان تسک نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'priority.in' => 'اولویت انتخاب‌شده نامعتبر است.',
            'due_date.date_format' => 'فرمت تاریخ انجام باید به صورت YYYY-MM-DD باشد.',
        ];
    }
}
