<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderController extends Controller
{
    public function index(Request $request): JsonResource
    {
        $query = Order::query()
            ->with(['items'])
            ->latest();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('order_number', 'like', "%{$search}%")
                ->orWhere('shipping_address_snapshot->first_name', 'like', "%{$search}%")
                ->orWhere('shipping_address_snapshot->last_name', 'like', "%{$search}%")
                ->orWhere('shipping_address_snapshot->phone', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->paginate(10);

        return OrderResource::collection($orders);
    }

    public function metrics(): \Illuminate\Http\JsonResponse
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();

        return response()->json([
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
        ]);
    }

    public function show(Order $order): JsonResource
    {
        $order->load(['items']);
        return OrderResource::make($order);
    }
}
