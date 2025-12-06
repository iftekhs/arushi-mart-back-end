<?php

namespace App\Services;

use App\Models\Customization;
use Illuminate\Support\Facades\Validator;

class CustomizationService
{
    /**
     * Validate customization data based on its fields schema
     */
    public function validateCustomization(Customization $customization, array $data): array
    {
        $rules = $this->buildValidationRules($customization->fields ?? []);

        return Validator::make($data, $rules)->validate();
    }

    /**
     * Build validation rules from fields configuration (array-based)
     */
    private function buildValidationRules(array $fields, string $prefix = ''): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $key = $field['key'] ?? null;
            if (!$key) {
                continue;
            }

            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if ($field['type'] === 'array' && isset($field['items'])) {
                // Array field - use the rules directly from the field
                $arrayRules = $field['rules'] ?? ['nullable', 'array'];
                $rules[$fullKey] = $arrayRules;

                // Build rules for array items
                $rules["{$fullKey}.*"] = ['required', 'array'];

                foreach ($field['items'] as $itemField) {
                    $itemKey = $itemField['key'] ?? null;
                    if (!$itemKey) {
                        continue;
                    }

                    $itemRules = $itemField['rules'] ?? [];
                    $rules["{$fullKey}.*.{$itemKey}"] = $itemRules;
                }
            } else {
                // Simple field - use rules directly
                $rules[$fullKey] = $field['rules'] ?? ['nullable'];
            }
        }

        return $rules;
    }
}
