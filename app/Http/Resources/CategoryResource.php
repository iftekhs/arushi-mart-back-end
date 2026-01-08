<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
                'description' => $this->description,
                'image' => path_to_url($this->image),
                'video' => path_to_url($this->video),
                'active' => (bool) $this->active,
                'featured' => (bool) $this->featured,
                'showcased' => (bool) $this->showcased,
                'products_count' => $this->when(isset($this->products_count), $this->products_count),
                'children_count' => $this->when(isset($this->children_count), $this->children_count),
            ],
            'relationships' => [
                'products' => $this->whenLoaded('products', function () {
                    return ProductResource::collection($this->products);
                }),
                'categories' => $this->whenLoaded('categories', function () {
                    return CategoryResource::collection($this->categories);
                }),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
