<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
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

    public function cancel(Order $order): \Illuminate\Http\JsonResponse
    {
        // Authorize using policy
        $this->authorize('cancel', $order);

        // Update order status to cancelled
        $order->status = OrderStatus::CANCELLED;

        // Update shipping status to canceled
        $order->shipping_status = ShippingStatus::CANCELED;

        $order->save();

        return response()->json([
            'message' => 'Order canceled successfully',
            'data' => OrderResource::make($order->load(['items']))
        ]);
    }

    public function updateShippingStatus(Request $request, Order $order): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'shipping_status' => ['required', 'string', 'in:pending,packaging,on_the_way,delivered']
        ]);

        // Authorize using policy
        $this->authorize('updateShippingStatus', $order);

        $newShippingStatus = ShippingStatus::from($request->shipping_status);

        // Update shipping status
        $order->shipping_status = $newShippingStatus;

        // Update order status based on shipping status
        switch ($newShippingStatus) {
            case ShippingStatus::PENDING:
                // If shipping status is changed to pending, update order status to pending
                $order->status = OrderStatus::PENDING;
                break;

            case ShippingStatus::DELIVERED:
                // If shipping status is delivered, mark order as completed
                $order->status = OrderStatus::COMPLETED;

                // If delivered and completed, mark payment as paid
                $order->payment_status = PaymentStatus::PAID;
                break;

            case ShippingStatus::PACKAGING:
            case ShippingStatus::ON_THE_WAY:
                // For packaging or on_the_way, set order status to processing
                $order->status = OrderStatus::PROCESSING;
                break;
        }

        $order->save();

        return response()->json([
            'message' => 'Shipping status updated successfully',
            'data' => OrderResource::make($order->load(['items']))
        ]);
    }
}
