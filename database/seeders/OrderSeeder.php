<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $products = Product::with(['variants.color', 'variants.size', 'images', 'categories'])->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No users or products found. Please run UserSeeder and ProductSeeder first.');
            return;
        }

        DB::transaction(function () use ($users, $products) {
            foreach ($users as $user) {
                // Create 1-5 orders per user
                $orderCount = rand(1, 5);

                for ($i = 0; $i < $orderCount; $i++) {
                    $this->createOrder($user, $products);
                }
            }
        });

        $this->command->info('Orders seeded successfully!');
    }

    private function createOrder(User $user, $products): void
    {
        $status = $this->getRandomStatus();
        $paymentMethod = PaymentMethod::CASH_ON_DELIVERY;
        
        // Determine payment and shipping status based on order status
        $paymentStatus = match ($status) {
            OrderStatus::COMPLETED => PaymentStatus::PAID,
            default => PaymentStatus::PENDING,
        };

        $shippingStatus = match ($status) {
            OrderStatus::COMPLETED => ShippingStatus::DELIVERED,
            OrderStatus::PROCESSING => ShippingStatus::PROCESSING,
            OrderStatus::CANCELLED => ShippingStatus::FAILED,
            default => ShippingStatus::PENDING,
        };

        $shippingAddress = [
            'first_name' => $user->first_name ?? 'John',
            'last_name' => $user->last_name ?? 'Doe',
            'email' => $user->email,
            'phone' => '017' . rand(10000000, 99999999),
            'address' => rand(1, 999) . ' Random Street',
            'city' => 'Dhaka',
            'postal_code' => '12' . rand(10, 99),
            'country' => 'Bangladesh',
        ];

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'status' => $status,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'shipping_method' => \App\Enums\ShippingMethod::INSIDE_DHAKA,
            'shipping_status' => $shippingStatus,
            'shipping_address_snapshot' => $shippingAddress,
            'shipping_cost' => 60.00, // Fixed shipping cost
            'total_amount' => 0, // Will update after adding items
        ]);

        $totalAmount = 0;
        $itemCount = rand(1, 5);

        for ($j = 0; $j < $itemCount; $j++) {
            $product = $products->random();
            $variant = $product->variants->isNotEmpty() ? $product->variants->random() : null;
            
            // Skip if product has no variants (shouldn't happen with current seeder but good for safety)
            if (!$variant && $product->variants->isNotEmpty()) continue;

            $quantity = rand(1, 3);
            $price = $product->price;
            $subtotal = $price * $quantity;

            $productSnapshot = [
                'id' => $product->id,
                'title' => $product->name,
                'slug' => $product->slug,
                'price' => $price,
                'image' => $product->images->first()?->path,
                'category' => [
                    'name' => $product->categories->first()?->name,
                    'slug' => $product->categories->first()?->slug,
                ],
                'color' => $variant ? [
                    'name' => $variant->color->name,
                    'hex_code' => $variant->color->hex_code,
                ] : null,
                'size' => ($variant && $variant->size) ? [
                    'name' => $variant->size->name,
                    'abbreviation' => $variant->size->abbreviation,
                ] : null,
            ];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal,
                'product_snapshot' => $productSnapshot,
            ]);

            $totalAmount += $subtotal;
        }

        // Update order total with shipping cost
        $order->update([
            'total_amount' => $totalAmount + $order->shipping_cost,
        ]);
    }

    private function getRandomStatus(): OrderStatus
    {
        $statuses = [
            OrderStatus::PENDING,
            OrderStatus::PROCESSING,
            OrderStatus::COMPLETED,
            OrderStatus::CANCELLED,
        ];

        return $statuses[array_rand($statuses)];
    }
}
