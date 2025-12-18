<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
                'name' => $this->name,
                'slug' => $this->slug,
                'price' => $this->price,
                'description' => $this->description,
                'sizeGuideUrl' => path_to_url($this->size_guide),
                'videoUrl' => path_to_url($this->video),
                'inStock' => $this->in_stock ?? false,
                'active' => $this->active,
                'featured' => $this->featured,
                'totalOrders' => $this->total_orders ?? 0,
                'totalStock' => $this->whenLoaded('variants', function () {
                    return $this->variants->sum('stock_quantity');
                }, 0),
            ],
            'relationships' => [
                'category' => CategoryResource::make($this->whenLoaded('category')),
                'categories' => CategoryResource::collection($this->whenLoaded('categories')),
                'tags' => TagResource::collection($this->whenLoaded('tags')),
                'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
                'images' => ProductImageResource::collection($this->whenLoaded('images')),
                'primaryImage' => ProductImageResource::make($this->whenLoaded('primaryImage')),
                'secondaryImage' => ProductImageResource::make($this->whenLoaded('secondaryImage')),
            ],
            'createdAt' => $this->created_at,
        ];
    }
}
