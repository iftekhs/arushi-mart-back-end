<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
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
                'sku' => $this->sku,
                'type' => $this->type->value,
                'stock_quantity' => $this->stock_quantity,
                'product_id' => $this->product_id,
                'color_id' => $this->color_id,
                'size_id' => $this->size_id,
            ],
            'relationships' => [
                'color' => new ColorResource($this->whenLoaded('color')),
                'size' => new SizeResource($this->whenLoaded('size')),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
