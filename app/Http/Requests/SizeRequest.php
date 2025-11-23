<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SizeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Will be restricted by middleware when admin system is implemented
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $sizeId = $this->route('size')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:10',
                Rule::unique('sizes', 'name')->ignore($sizeId),
            ],
            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],
            'active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
