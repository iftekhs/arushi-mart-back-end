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
                'stockQuantity' => $this->stock_quantity,
                'productId' => $this->product_id,
                'colorId' => $this->color_id,
                'sizeId' => $this->size_id,
            ],
            'relationships' => [
                'color' => new ColorResource($this->whenLoaded('color')),
                'size' => new SizeResource($this->whenLoaded('size')),
            ],
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
