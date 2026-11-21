<?php

namespace App\Http\Requests\Subtask;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubtaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان زیرتسک الزامی است.',
            'title.max' => 'عنوان زیرتسک نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        ];
    }
}
