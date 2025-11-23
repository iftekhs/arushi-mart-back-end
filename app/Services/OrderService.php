<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductColorVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function createOrder($user, array $cartItems, array $shippingAddress, string $paymentMethod, string $shippingMethod): Order
    {
        return DB::transaction(function () use ($user, $cartItems, $shippingAddress, $paymentMethod, $shippingMethod) {
            $shippingCost = $this->calculateShipping($shippingMethod);
            $totalAmount = $this->calculateTotal($cartItems, $shippingCost);
            $orderNumber = 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

            $order = Order::create([
                'order_number' => $orderNumber,
                'status' => OrderStatus::PENDING,
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::PENDING,
                'shipping_method' => $shippingMethod,
                'shipping_status' => ShippingStatus::PENDING,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'user_id' => $user->id,
                'shipping_address' => [
                    'first_name' => $shippingAddress['first_name'],
                    'last_name' => $shippingAddress['last_name'],
                    'address' => $shippingAddress['address'],
                    'apartment' => $shippingAddress['apartment'] ?? null,
                    'city' => $shippingAddress['city'],
                    'postal_code' => $shippingAddress['postal_code'],
                    'phone' => $shippingAddress['phone'],
                ],
            ]);

            foreach ($cartItems as $item) {
                $variant = ProductColorVariant::with([
                    'product.category',
                    'productColor.color',
                    'productColor.images',
                    'size'
                ])->findOrFail($item['product_color_variant_id']);

                $productSnapshot = $this->buildProductSnapshot($variant);
                $subtotal = $item['price'] * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_color_id' => $item['product_color_id'],
                    'product_color_variant_id' => $item['product_color_variant_id'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                    'product_snapshot' => $productSnapshot,
                ]);

                $variant->stock_quantity -= $item['quantity'];
                $variant->save();
            }

            return $order->load('orderItems');
        });
    }

    public function buildProductSnapshot(ProductColorVariant $variant): array
    {
        $product = $variant->product;
        $productColor = $variant->productColor;
        $color = $productColor->color;
        $size = $variant->size;
        $firstImage = $productColor->images->first();

        return [
            'title' => $product->name ?? $product->title ?? '',
            'description' => $product->description ?? '',
            'slug' => $product->slug ?? '',
            'color' => [
                'name' => $color->name ?? '',
                'hex_code' => $color->hex_code ?? '',
                'image' => $firstImage ? $firstImage->path : null,
            ],
            'size' => [
                'name' => $size ? $size->name : null,
            ],
            'category' => [
                'name' => $product->category->name ?? '',
                'slug' => $product->category->slug ?? '',
            ],
        ];
    }

    public function calculateShipping(string $method): float
    {
        return match ($method) {
            'express' => 100.00,
            'standard' => 50.00,
            default => 50.00,
        };
    }

    public function calculateTotal(array $cartItems, float $shipping): float
    {
        $subtotal = array_reduce($cartItems, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        return $subtotal + $shipping;
    }
}
