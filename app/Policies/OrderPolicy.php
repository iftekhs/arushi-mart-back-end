<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    /**
     * Determine if the order can be canceled.
     * Only pending orders can be canceled.
     */
    public function cancel(?User $user, Order $order): bool
    {
        return $order->status === OrderStatus::PENDING;
    }

    /**
     * Determine if the order's shipping status can be updated.
     * Completed orders cannot have their shipping status changed.
     */
    public function updateShippingStatus(?User $user, Order $order): bool
    {
        return $order->status !== OrderStatus::COMPLETED;
    }
}
