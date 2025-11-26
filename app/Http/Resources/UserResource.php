<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $ordersCount = $this->orders_count ?? 0;
        $totalSpent = $this->orders_sum_total_amount ?? 0;
        $averageOrderValue = $ordersCount > 0 ? $totalSpent / $ordersCount : 0;

        return [
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role->value,
                'emailVerifiedAt' => $this->email_verified_at,
                'ordersCount' => $ordersCount,
                'totalSpent' => $totalSpent,
                'averageOrderValue' => round($averageOrderValue, 2),
            ],
            'createdAt' => $this->created_at,
        ];
    }
}
