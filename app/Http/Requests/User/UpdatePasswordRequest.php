<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'different:current_password', Password::min(8)],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'کلمه عبور فعلی الزامی است.',
            'current_password.current_password' => 'کلمه عبور فعلی اشتباه است.',
            'password.required' => 'کلمه عبور جدید الزامی است.',
            'password.min' => 'کلمه عبور جدید باید حداقل ۸ کاراکتر باشد.',
            'password.confirmed' => 'تکرار کلمه عبور جدید مطابقت ندارد.',
            'password.different' => 'کلمه عبور جدید نباید مشابه کلمه عبور فعلی باشد.',
        ];
    }
}
