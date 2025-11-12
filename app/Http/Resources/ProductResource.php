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
                'sku' => $this->sku,
                'name' => $this->name,
                'slug' => $this->slug,
                'price' => $this->price,
                'description' => $this->description,
                'active' => $this->active,
            ],
            'relationships' => [
                'categories' => CategoryResource::collection($this->whenLoaded('categories')),
                'tags' => TagResource::collection($this->whenLoaded('tags')),
                'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
                'images' => ProductImageResource::collection($this->whenLoaded('images')),
                'primaryImage' => ProductImageResource::make($this->whenLoaded('primaryImage')),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
