<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
            'active' => ['boolean'],
            'featured' => ['boolean'],
            'showcased' => ['boolean'],
            'image' => ['nullable', 'string'], // Assuming URL or path for now
            'video' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ];
    }
}
