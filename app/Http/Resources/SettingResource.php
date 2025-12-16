<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
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
                'key' => $this->key,
                'value' => $this->transformImagePaths($this->value ?? []),
            ],
        ];
    }

    /**
     * Transform image paths in the value for SEO settings
     */
    private function transformImagePaths(array $value): array
    {
        // Check if this is the seo settings
        if (isset($value['seo']['global'])) {
            $imageFields = ['og_image', 'twitter_image', 'favicon', 'apple_icon'];
            foreach ($imageFields as $field) {
                if (isset($value['seo']['global'][$field])) {
                    $value['seo']['global'][$field] = path_to_url($value['seo']['global'][$field]);
                }
            }
        }

        return $value;
    }
}
