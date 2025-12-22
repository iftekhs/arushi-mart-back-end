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
                    
                    // For image fields, allow '-1' for removal
                    if ($itemField['type'] === 'image') {
                        $itemRules = $this->adjustImageRules($itemRules);
                    }
                    
                    // For markdown fields, use custom validation
                    if ($itemField['type'] === 'markdown') {
                        $itemRules = $this->adjustMarkdownRules($itemRules);
                    }
                    
                    $rules["{$fullKey}.*.{$itemKey}"] = $itemRules;
                }
            } else {
                // Simple field - use rules directly
                $fieldRules = $field['rules'] ?? ['nullable'];
                
                // For image fields, allow '-1' for removal
                if ($field['type'] === 'image') {
                    $fieldRules = $this->adjustImageRules($fieldRules);
                }
                
                // For markdown fields, use custom validation
                if ($field['type'] === 'markdown') {
                    $fieldRules = $this->adjustMarkdownRules($fieldRules);
                }
                
                $rules[$fullKey] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Adjust image validation rules to allow '-1' for removal and make nullable
     */
    private function adjustImageRules(array $rules): array
    {
        // Remove 'required' rule for image fields (they should be nullable on update)
        $rules = array_filter($rules, fn($rule) => $rule !== 'required');
        
        // Ensure 'nullable' is present
        if (!in_array('nullable', $rules)) {
            array_unshift($rules, 'nullable');
        }
        
        // Check if 'image' rule exists
        $hasImageRule = in_array('image', $rules);
        
        if ($hasImageRule) {
            // Remove 'image' rule and add custom rule that accepts image or '-1'
            $rules = array_filter($rules, fn($rule) => $rule !== 'image');
            
            // Add custom validation: must be image file OR string '-1'
            $rules[] = function ($attribute, $value, $fail) {
                if ($value === '-1') {
                    return; // Allow '-1' for removal
                }
                
                if (!($value instanceof \Illuminate\Http\UploadedFile)) {
                    $fail("The {$attribute} field must be an image.");
                    return;
                }
                
                // Validate as image
                $validator = validator(
                    [$attribute => $value],
                    [$attribute => 'image']
                );
                
                if ($validator->fails()) {
                    $fail($validator->errors()->first($attribute));
                }
            };
        }
        
        return $rules;
    }

    /**
     * Adjust markdown validation rules to use custom MarkdownMaxLength rule
     */
    private function adjustMarkdownRules(array $rules): array
    {
        $adjustedRules = [];
        
        foreach ($rules as $rule) {
            // Check if it's a max:X rule
            if (is_string($rule) && preg_match('/^max:(\d+)$/', $rule, $matches)) {
                $maxLength = (int) $matches[1];
                // Replace with custom MarkdownMaxLength rule
                $adjustedRules[] = new \App\Rules\MarkdownMaxLength($maxLength);
            } else {
                // Keep other rules as-is
                $adjustedRules[] = $rule;
            }
        }
        
        return $adjustedRules;
    }
}
