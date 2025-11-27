<?php

namespace App\Http\Requests;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;

class SyncTagsRequest extends FormRequest
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
        return [
            'tags' => ['required', 'array', 'min:1'],
            'tags.*.id' => ['sometimes', 'integer', 'exists:tags,id'],
            'tags.*.name' => ['required', 'string', 'max:50'],
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
            'tags.required' => 'At least one tag is required.',
            'tags.*.name.required' => 'Tag name is required.',
            'tags.*.name.max' => 'Tag name must not exceed 50 characters.',
        ];
    }
}
