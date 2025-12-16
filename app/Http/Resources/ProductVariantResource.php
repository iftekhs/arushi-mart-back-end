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
                'stockQuantity' => $this->stock_quantity,
                'productId' => $this->product_id,
                'colorId' => $this->color_id,
                'sizeId' => $this->size_id,
            ],
            'relationships' => [
                'color' => ColorResource::make($this->whenLoaded('color')),
                'size' => SizeResource::make($this->whenLoaded('size')),
                'product' => ProductResource::make($this->whenLoaded('product')),
            ],
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
