<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attributes' => [
                'orderNumber' => $this->order_number,
                'status' => $this->status,
                'paymentMethod' => $this->payment_method,
                'paymentStatus' => $this->payment_status,
                'shippingMethod' => $this->shipping_method,
                'shippingStatus' => $this->shipping_status,
                'shippingCost' => (float) $this->shipping_cost,
                'totalAmount' => (float) $this->total_amount,
                'shippingAddressSnapshot' => $this->shipping_address_snapshot,
            ],
            'relationships' => [
                'items' => OrderItemResource::collection($this->whenLoaded('items')),
            ],
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
