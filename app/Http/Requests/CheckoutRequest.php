<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'email' => [Rule::requiredIf(!$isAuthenticated), 'email', 'max:255'],
            'cart_items' => ['required', 'array', 'min:1'],
            'cart_items.*.product_id' => ['required', 'integer'],
            'cart_items.*.variant_id' => ['required', 'integer'],
            'cart_items.*.quantity' => ['required', 'integer', 'min:1'],
            'shipping_address_id' => ['nullable', 'integer', 'exists:shipping_addresses,id'],
            'shipping_address' => [!$isAuthenticated ? 'required' : 'required_without:shipping_address_id', 'array'],
            'shipping_address.full_name' => ['required_with:shipping_address', 'string', 'max:255'],
            'shipping_address.address' => ['required_with:shipping_address', 'string', 'max:500'],
            'shipping_address.phone' => ['required_with:shipping_address', 'string', 'max:20'],
            'payment_method' => ['required', 'string', Rule::in(PaymentMethod::valuesForUser())],
            'shipping_method' => ['required', 'string', Rule::in(ShippingMethod::valuesForUser())],
        ];
    }
}
