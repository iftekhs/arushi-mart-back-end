<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserOrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderController extends Controller
{
    public function index(Request $request): JsonResource
    {
        $query = $request->user()
            ->orders()
            ->with('items')
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->paginate(10);

        return UserOrderResource::collection($orders);
    }

    public function show(Order $order): JsonResource
    {
        $order->load('items');

        return UserOrderResource::make($order);
    }
}
