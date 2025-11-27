<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('categories')->ignore($this->category)],
            'description' => ['nullable', 'string'],
            'active' => ['boolean'],
            'featured' => ['boolean'],
            'showcased' => ['boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
            'video' => ['nullable', 'mimetypes:video/mp4,video/mpeg,video/quicktime', 'max:51200'],
            'parent_id' => ['nullable', 'exists:categories,id', Rule::notIn([$this->category->id])], // Prevent self-parenting
        ];
    }
}
