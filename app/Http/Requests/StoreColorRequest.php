<?php

namespace App\Http\Requests;

use App\Models\Color;
use Illuminate\Foundation\Http\FormRequest;

class StoreColorRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Check if we've reached the limit of 100 colors
        if (Color::count() >= 100) {
            abort(422, 'Maximum limit of 100 colors reached. Please delete some colors before adding new ones.');
        }

        return [
            'name' => ['required', 'string', 'max:50', 'unique:colors,name'],
            'hex_code' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
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
            'hex_code.regex' => 'The hex code must be a valid hexadecimal color code (e.g., #FF5733 or #F57).',
        ];
    }
}
