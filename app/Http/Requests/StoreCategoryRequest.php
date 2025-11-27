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
            'image' => ['nullable', 'image', 'max:5120'], // Max 5MB
            'video' => ['nullable', 'mimetypes:video/mp4,video/mpeg,video/quicktime', 'max:51200'], // Max 50MB
            'parent_id' => ['nullable', 'exists:categories,id'],
        ];
    }
}
