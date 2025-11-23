<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
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
                $variant = ProductVariant::with([
                    'product.category',
                    'product.primaryImage',
                    'color',
                    'size'
                ])->findOrFail($item['variant_id']);

                $productSnapshot = $this->buildProductSnapshot($variant);
                $price = (float) $variant->product->price;
                $subtotal = $price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'price' => $price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                    'product_snapshot' => $productSnapshot,
                ]);

                $variant->stock_quantity -= $item['quantity'];
                $variant->save();
            }

            return $order->load('items');
        });
    }

    public function buildProductSnapshot(ProductVariant $variant): array
    {
        $product = $variant->product;
        $color = $variant->color;
        $size = $variant->size;
        $primaryImage = $product->primaryImage;

        return [
            'title' => $product->name ?? '',
            'description' => $product->description ?? '',
            'slug' => $product->slug ?? '',
            'sku' => $product->sku ?? '',
            'color' => [
                'name' => $color->name ?? '',
                'hex_code' => $color->hex_code ?? '',
            ],
            'size' => [
                'name' => $size ? $size->name : null,
            ],
            'image' => $primaryImage ? $primaryImage->path : null,
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
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $variant = ProductVariant::with('product')->find($item['variant_id']);
            if ($variant && $variant->product && $variant->product->active) {
                $subtotal += (float) $variant->product->price * $item['quantity'];
            }
        }

        return $subtotal + $shipping;
    }
}
