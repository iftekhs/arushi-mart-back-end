<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Enums\ShippingMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon; // Import Carbon for date manipulation

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
            // --- Logic for Seeding Across the Current Month ---
            $currentDate = Carbon::now();
            $startOfMonth = $currentDate->copy()->startOfMonth();
            $daysInMonth = $currentDate->daysInMonth; // Or $currentDate->day to seed up to today

            $this->command->info("Seeding orders across the days of {$currentDate->format('F Y')}");

            // Iterate through each day of the current month (or up to today)
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $seedingDate = $startOfMonth->copy()->day($d);

                // Determine the number of users who will place orders on this day
                // (e.g., 5% to 15% of total users)
                $activeUserCount = rand(ceil($users->count() * 0.05), ceil($users->count() * 0.15));
                
                // Ensure we don't try to select more users than available
                $activeUserCount = min($activeUserCount, $users->count()); 

                // Get a random subset of users
                $activeUsers = $users->random($activeUserCount);

                $this->command->line("  -> Orders for: {$seedingDate->format('Y-m-d')}");

                foreach ($activeUsers as $user) {
                    // Create 1-2 orders per active user per day
                    $orderCount = rand(1, 2);
                    for ($i = 0; $i < $orderCount; $i++) {
                        // Pass the current day's Carbon object to the createOrder method
                        $this->createOrder($user, $products, $seedingDate);
                    }
                }
            }
        });

        $this->command->info('Orders seeded successfully across the current month!');
    }

    private function createOrder(User $user, $products, Carbon $seedingDate): void
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
            OrderStatus::PROCESSING => ShippingStatus::PACKAGING,
            OrderStatus::CANCELED => ShippingStatus::DELIVERED,
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

        // --- Date Logic Start ---
        // $seedingDate is already set to the correct day. Now add a random time.
        $orderDateTime = $seedingDate->copy()->hour(rand(9, 21))->minute(rand(0, 59))->second(rand(0, 59));

        // Set 'updated_at' to be a short random time after 'created_at' for all non-pending orders
        $updatedAt = $orderDateTime->copy()->addMinutes(rand(10, 1440)); // 10 minutes to 24 hours later

        // For completed orders, the updated_at should reflect when it was completed (a few days later)
        if ($status === OrderStatus::COMPLETED) {
            $updatedAt = $orderDateTime->copy()->addDays(rand(3, 7));
        } elseif ($status === OrderStatus::CANCELED) {
            // Canceled orders are updated (canceled) within 1-2 days
            $updatedAt = $orderDateTime->copy()->addDays(rand(1, 2));
        }

        // Ensure updated_at is not earlier than created_at
        $updatedAt = $updatedAt->greaterThan($orderDateTime) ? $updatedAt : $orderDateTime->copy()->addMinute();
        // --- Date Logic End ---

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'status' => $status,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'shipping_method' => ShippingMethod::INSIDE_DHAKA,
            'shipping_status' => $shippingStatus,
            'shipping_address_snapshot' => $shippingAddress,
            'shipping_cost' => 60.00,
            'total_amount' => 0,
            'created_at' => $orderDateTime, // Set the creation date and time
            'updated_at' => $updatedAt, // Set the update date
        ]);

        $totalAmount = 0;
        $itemCount = rand(1, 5);

        for ($j = 0; $j < $itemCount; $j++) {
            $product = $products->random();
            $variant = $product->variants->isNotEmpty() ? $product->variants->random() : null;

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
                'created_at' => $orderDateTime,
                'updated_at' => $updatedAt,
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
            OrderStatus::CANCELED,
        ];

        return $statuses[array_rand($statuses)];
    }
}