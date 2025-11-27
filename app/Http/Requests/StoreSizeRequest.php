<?php

namespace App\Http\Requests;

use App\Models\Size;
use Illuminate\Foundation\Http\FormRequest;

class StoreSizeRequest extends FormRequest
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
        // Check if we've reached the limit of 100 sizes
        if (Size::count() >= 100) {
            abort(422, 'Maximum limit of 100 sizes reached. Please delete some sizes before adding new ones.');
        }

        return [
            'name' => ['required', 'string', 'max:50', 'unique:sizes,name'],
            'abbreviation' => ['required', 'string', 'max:3'],
        ];
    }
}
