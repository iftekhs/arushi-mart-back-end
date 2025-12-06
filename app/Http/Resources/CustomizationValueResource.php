<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomizationValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->transformImagePaths($this->value ?? [], $this->fields ?? []);
    }

    /**
     * Transform image paths in the value based on fields schema
     */
    private function transformImagePaths(array $value, array $fields): array
    {
        foreach ($fields as $field) {
            $key = $field['key'] ?? null;
            if (!$key || !isset($value[$key])) continue;

            if ($field['type'] === 'array' && isset($field['items'])) {
                // Transform array fields
                if (is_array($value[$key])) {
                    foreach ($value[$key] as $index => $item) {
                        if (!is_array($item)) continue;

                        foreach ($field['items'] as $itemField) {
                            $itemKey = $itemField['key'] ?? null;
                            if (!$itemKey || !isset($item[$itemKey])) continue;

                            if ($itemField['type'] === 'image') {
                                $value[$key][$index][$itemKey] = path_to_url($item[$itemKey]);
                            }
                        }
                    }
                }
            } elseif ($field['type'] === 'image') {
                // Transform single image field
                $value[$key] = path_to_url($value[$key]);
            }
        }

        return $value;
    }
}
