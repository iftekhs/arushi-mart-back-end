<?php

namespace App\Http\Requests;

use App\Enums\ProductVariantType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product')->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'description' => ['nullable', 'string', 'max:1000'],

            'active' => ['required', 'boolean'],
            'featured' => ['required', 'boolean'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where(fn($query) => $query->where('active', true))],

            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')->where(fn($query) => $query->where('active', true))],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],

            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.color.id' => ['required', 'integer', 'exists:colors,id'],

            'variants.*.color.images' => ['required', 'array', 'min:1'],
            'variants.*.color.images.*.id' => ['nullable', 'integer', 'exists:product_images,id'],
            'variants.*.color.images.*.primary' => ['required', 'boolean'],
            'variants.*.color.images.*.file' => ['nullable', 'image', 'max:1024'],

            'variants.*.size_id' => ['required', 'integer', 'exists:sizes,id'],
            'variants.*.type' => ['required', 'string', Rule::in(ProductVariantType::values())],
            'variants.*.sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('product_variants', 'sku')->ignore($productId, 'product_id')
            ],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'variants.*.color.images.*.file.size.regex' => 'The file size format is invalid. It should be in the format X.XXXKB or X.XXXMB, etc.',
        ];
    }
}
