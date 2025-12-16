<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'size_guide' => ['nullable', 'image', 'max:2048'],

            'active' => ['required', 'boolean'],
            'featured' => ['required', 'boolean'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where(fn($query) => $query->where('active', true))],

            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')->where(fn($query) => $query->where('active', true))],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],

            'variants' => ['required', 'array', 'min:1'],
            'variants.*.color.id' => ['required', 'integer', 'exists:colors,id'],

            'variants.*.color.images' => ['required', 'array', 'min:1'],
            'variants.*.color.images.*.primary' => ['required', 'boolean'],
            'variants.*.color.images.*.file' => ['required', 'image', 'max:2048'],

            'variants.*.size_id' => ['required', 'integer', 'exists:sizes,id'],
            'variants.*.auto_generate_sku' => ['nullable', 'boolean'],
            'variants.*.sku' => [
                'required_if:variants.*.auto_generate_sku,false',
                'nullable',
                'string',
                'max:50',
                'unique:product_variants,sku'
            ],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'variants.required' => 'At least one product variant is required.',
            'variants.*.sku.unique' => 'The SKU :input is already in use. Please choose a different SKU.',
            'variants.*.color.images.*.file.size.regex' => 'The file size format is invalid. It should be in the format X.XXXKB or X.XXXMB, etc.',
        ];
    }
}
