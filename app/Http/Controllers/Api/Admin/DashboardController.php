<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function metrics(): JsonResponse
    {
        $totalCustomers = User::where('role', UserRole::USER)->count();
        $totalOrders = Order::count();

        // New customers in last 24 hours
        $newCustomers24h = User::where('role', UserRole::USER)
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

    public function analytics(): JsonResponse
    {
        // Get current month data
        $currentMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Average Order Value
        $currentMonthOrders = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $currentMonthStart)
            ->count();
        $currentMonthSales = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $currentMonthStart)
            ->sum('total_amount');
        $avgOrderValue = $currentMonthOrders > 0 ? $currentMonthSales / $currentMonthOrders : 0;

        $lastMonthOrders = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();
        $lastMonthSales = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total_amount');
        $lastAvgOrderValue = $lastMonthOrders > 0 ? $lastMonthSales / $lastMonthOrders : 0;
        $avgOrderValueChange = $lastAvgOrderValue > 0
            ? (($avgOrderValue - $lastAvgOrderValue) / $lastAvgOrderValue) * 100
            : 0;

        // Average Sales (daily average for current month)
        $daysInMonth = now()->day;
        $avgDailySales = $daysInMonth > 0 ? $currentMonthSales / $daysInMonth : 0;

        $lastMonthDays = now()->subMonth()->daysInMonth;
        $lastAvgDailySales = $lastMonthDays > 0 ? $lastMonthSales / $lastMonthDays : 0;
        $avgSalesChange = $lastAvgDailySales > 0
            ? (($avgDailySales - $lastAvgDailySales) / $lastAvgDailySales) * 100
            : 0;

        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(6)
            ->with(['product' => function ($query) {
                $query->select('id', 'name', 'price')->with('primaryImage');
            }])
            ->get();

        $topProductsList = $topProducts->map(function ($item) {
            return [
                'name' => $item->product->name,
                'price' => (float) $item->product->price,
                'image' => $item->product->primaryImage ? $item->product->primaryImage->path : null,
                'total_sold' => (int) $item->total_sold,
            ];
        });

        // Mini chart data (last 28 days)
        $chartData = [];
        for ($i = 27; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailySales = Order::where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            $chartData[] = (float) $dailySales;
        }

        return response()->json([
            'average_order_value' => [
                'value' => round($avgOrderValue, 2),
                'change' => round($avgOrderValueChange, 2),
                'chart_data' => $chartData,
            ],
            'average_sales' => [
                'value' => round($avgDailySales, 2),
                'change' => round($avgSalesChange, 2),
                'chart_data' => $chartData,
            ],
            'top_products' => $topProductsList,
        ]);
    }
}
