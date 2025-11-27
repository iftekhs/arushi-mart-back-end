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
                'active' => $this->active,
                'featured' => $this->featured,
                'showcased' => $this->showcased,
            ],
            'relationships' => [
                'products' => $this->whenLoaded('products', function () {
                    return ProductResource::collection($this->products);
                }),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
