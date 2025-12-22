<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Enums\ShippingStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function createOrder(User|null $user, array $cartItems, ?array $shippingAddress, PaymentMethod $paymentMethod, ShippingMethod $shippingMethod): Order
    {
        return DB::transaction(function () use ($user, $cartItems, $shippingAddress, $paymentMethod, $shippingMethod) {
            $shippingCost = $this->calculateShipping($shippingMethod);
            $totalAmount = $this->calculateTotal($cartItems, $shippingCost);
            
            // Generate incremental order number
            $lastOrder = Order::orderBy('id', 'desc')->first();
            if ($lastOrder && $lastOrder->order_number) {
                // Extract the numeric part from the last order number (e.g., "AM-0001" -> 1)
                $lastNumber = (int) str_replace('AM-', '', $lastOrder->order_number);
                $nextNumber = $lastNumber + 1;
            } else {
                // Start from 1 if no orders exist
                $nextNumber = 1;
            }
            $orderNumber = 'AM-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            
            $shippingAddressSnapshot = $this->generateShippingAddressSnapshot($shippingMethod, $shippingAddress);

            // Determine payment status based on payment method
            $paymentStatus = $paymentMethod === PaymentMethod::CASH
                ? PaymentStatus::PAID
                : PaymentStatus::PENDING;

            // Determine shipping status and order status based on shipping method
            $shippingStatus = $shippingMethod === ShippingMethod::ON_SITE
                ? ShippingStatus::DELIVERED
                : ShippingStatus::PENDING;

            $orderStatus = $shippingMethod === ShippingMethod::ON_SITE
                ? OrderStatus::COMPLETED
                : OrderStatus::PENDING;

            $order = Order::create([
                'order_number' => $orderNumber,
                'status' => $orderStatus,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'shipping_method' => $shippingMethod,
                'shipping_status' => $shippingStatus,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'user_id' => $user?->id,
                'shipping_address_snapshot' => $shippingAddressSnapshot,
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

    private function generateShippingAddressSnapshot(ShippingMethod $shippingMethod, ?array $shippingAddress): ?array
    {
        if ($shippingMethod === ShippingMethod::ON_SITE) return null;

        return $shippingAddress ? [
            'first_name' => $shippingAddress['first_name'],
            'last_name' => $shippingAddress['last_name'],
            'address' => $shippingAddress['address'],
            'apartment' => $shippingAddress['apartment'] ?? null,
            'city' => $shippingAddress['city'],
            'postal_code' => $shippingAddress['postal_code'],
            'phone' => $shippingAddress['phone'],
        ] : null;
    }

    private function calculateShipping(ShippingMethod $method): float
    {
        $setting = Setting::where('key', 'business')->first();
        $value = $setting?->value ?? [];

        return match ($method) {
            ShippingMethod::ON_SITE => $value['on_site_fee'] ?? 0.00,
            ShippingMethod::INSIDE_DHAKA => $value['inside_dhaka_fee'] ?? 60.00,
            ShippingMethod::OUTSIDE_DHAKA => $value['outside_dhaka_fee'] ?? 120.00,
            default => 0.00,
        };
    }

    private function calculateTotal(array $cartItems, float $shipping): float
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
