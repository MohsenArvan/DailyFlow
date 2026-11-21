<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'in:none,low,medium,high,urgent'],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
            'due_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'project_id' => ['nullable', 'integer'],
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
