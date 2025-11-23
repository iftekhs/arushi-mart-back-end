<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
        $isAuthenticated = $this->user();

        return [
            'email' => ['nullable', 'email', 'max:255'],
            'cart_items' => ['required', 'array', 'min:1'],
            'cart_items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'cart_items.*.product_color_id' => ['required', 'integer', 'exists:product_colors,id'],
            'cart_items.*.product_color_variant_id' => ['required', 'integer', 'exists:product_color_variants,id'],
            'cart_items.*.quantity' => ['required', 'integer', 'min:1'],
            'cart_items.*.price' => ['required', 'numeric', 'min:0'],
            'shipping_address_id' => ['nullable', 'integer', 'exists:shipping_addresses,id'],
            'shipping_address' => [!$isAuthenticated ? 'required' : 'required_without:shipping_address_id', 'array'],
            'shipping_address.first_name' => ['required_with:shipping_address', 'string', 'max:255'],
            'shipping_address.last_name' => ['required_with:shipping_address', 'string', 'max:255'],
            'shipping_address.address' => ['required_with:shipping_address', 'string', 'max:500'],
            'shipping_address.apartment' => ['nullable', 'string', 'max:255'],
            'shipping_address.city' => ['required_with:shipping_address', 'string', 'max:255'],
            'shipping_address.postal_code' => ['required_with:shipping_address', 'string', 'max:20'],
            'shipping_address.phone' => ['required_with:shipping_address', 'string', 'max:20'],
            'payment_method' => ['required', 'string', 'in:cod,online'],
            'shipping_method' => ['required', 'string', 'in:standard,express'],
        ];
    }
}
