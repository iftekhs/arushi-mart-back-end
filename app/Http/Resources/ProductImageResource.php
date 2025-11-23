<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
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
                'path' => $this->path,
                'primary' => $this->primary,
                'sort_order' => $this->sort_order,
                'product_id' => $this->product_id,
                'color_id' => $this->color_id,
            ],
            'relationships' => [
                'color' => ColorResource::make($this->whenLoaded('color')),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
