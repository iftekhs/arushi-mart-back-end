<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): JsonResource
    {
        $query = Order::query()
            ->with('items')
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

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->has('shipping_status')) {
            $query->where('shipping_status', $request->input('shipping_status'));
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

    public function cancel(Order $order): JsonResponse
    {
        $order->update([
            'status' => OrderStatus::CANCELED,
            'shipping_status' => ShippingStatus::CANCELED,
        ]);

        return response()->json([
            'message' => 'Order canceled successfully',
            'data' => OrderResource::make($order->load(['items']))
        ]);
    }

    public function updateShippingStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'shipping_status' => ['required', 'string', Rule::in(ShippingStatus::values())]
        ]);

        $newShippingStatus = ShippingStatus::from($request->shipping_status);

        $order->shipping_status = $newShippingStatus;

        switch ($newShippingStatus) {
            case ShippingStatus::PENDING:
                $order->status = OrderStatus::PENDING;
                break;

            case ShippingStatus::DELIVERED:
                $order->status = OrderStatus::COMPLETED;
                $order->payment_status = PaymentStatus::PAID;
                break;

            case ShippingStatus::PACKAGING:
            case ShippingStatus::ON_THE_WAY:
                $order->status = OrderStatus::PROCESSING;
                break;
        }

        $order->update();

    }

    public function store(Request $request): JsonResource
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'cart_items' => 'required|array|min:1',
            'cart_items.*.product_id' => 'required|integer|exists:products,id',
            'cart_items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'cart_items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|array',
            'shipping_address.first_name' => 'required|string|max:255',
            'shipping_address.last_name' => 'required|string|max:255',
            'shipping_address.address' => 'required|string|max:500',
            'shipping_address.apartment' => 'nullable|string|max:255',
            'shipping_address.city' => 'required|string|max:255',
            'shipping_address.postal_code' => 'required|string|max:20',
            'shipping_address.phone' => 'required|string|max:20',
            'payment_method' => 'required|string|in:cod,online',
            'shipping_method' => 'required|string|in:standard,express',
        ]);

        // Find or create user by email
        $user = \App\Models\User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['shipping_address']['first_name'] . ' ' . $validated['shipping_address']['last_name'],
                'password' => bcrypt(\Illuminate\Support\Str::random(32)),
            ]
        );

        // Create order using OrderService
        $orderService = app(\App\Services\OrderService::class);
        $order = $orderService->createOrder(
            $user,
            $validated['cart_items'],
            $validated['shipping_address'],
            $validated['payment_method'],
            $validated['shipping_method']
        );

        return OrderResource::make($order);
    }
}
