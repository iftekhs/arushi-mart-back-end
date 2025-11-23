<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders
     */
    public function index(Request $request): JsonResource
    {
        $query = $request->user()
            ->orders()
            ->with('items')
            ->latest();

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->paginate(15);

        return OrderResource::collection($orders);
    }

    /**
     * Display the specified order
     */
    public function show(Order $order): JsonResource
    {
        $order->load('items');

        return OrderResource::make($order);
    }
}
