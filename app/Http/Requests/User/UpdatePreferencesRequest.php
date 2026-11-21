<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
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
            'theme' => ['sometimes', 'string', 'in:dark,light,system'],
            'date_format' => ['sometimes', 'string', 'in:jalali,gregorian'],
            'start_of_week' => ['sometimes', 'integer', 'in:0,1,2,3,4,5,6'],
            'wallpaper' => ['sometimes', 'required', 'string', 'in:lake,dunes,coast,none'],
            'daily_goal' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
