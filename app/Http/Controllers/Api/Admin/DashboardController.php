<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function metrics(): JsonResponse
    {
        $totalCustomers = User::where('role', 'customer')->count();
        $totalOrders = Order::count();

        // New customers in last 24 hours
        $newCustomers24h = User::where('role', 'customer')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $totalProducts = Product::count();
        $totalSales = Order::where('payment_status', 'paid')->sum('total_amount');

        return response()->json([
            'total_customers' => $totalCustomers,
            'total_orders' => $totalOrders,
            'new_customers_24h' => $newCustomers24h,
            'total_products' => $totalProducts,
            'total_sales' => $totalSales,
        ]);
    }

    public function salesOverview(): JsonResponse
    {
        $salesData = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Fill in missing dates with zero sales
        $result = [];
        $currentDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();

        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $existingData = $salesData->firstWhere('date', $dateString);

            $result[] = [
                'date' => $dateString,
                'total_sales' => $existingData ? (float) $existingData->total_sales : 0,
                'order_count' => $existingData ? $existingData->order_count : 0,
            ];

            $currentDate->addDay();
        }

        return response()->json($result);
    }

    public function latestOrders(): JsonResponse
    {
        $orders = Order::with('items')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'data' => OrderResource::collection($orders)
        ]);
    }
}
