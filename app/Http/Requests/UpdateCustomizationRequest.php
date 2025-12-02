<?php

namespace App\Http\Requests;

use App\Models\Customization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateCustomizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            '*.key' => 'required|string|exists:customizations,key',
            '*.data' => 'required|array',
        ];
    }

    /**
     * Validate the data against field definitions from database
     */
    public function validateWithFieldDefinitions(): array
    {
        $validated = [];

        foreach ($this->input() as $item) {
            $customization = Customization::where('key', $item['key'])->first();
            
            if (!$customization) {
                throw ValidationException::withMessages([
                    $item['key'] => "Customization with key '{$item['key']}' not found."
                ]);
            }

            $fields = $customization->fields ?? [];
            $data = $item['data'] ?? [];
            
            // Build dynamic validation rules from field definitions
            $rules = $this->buildValidationRules($fields, $data);
            
            // Validate the data
            $validator = validator($data, $rules);
            
            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    $item['key'] => $validator->errors()->all()
                ]);
            }

            $validated[] = [
                'key' => $item['key'],
                'data' => $validator->validated(),
            ];
        }

        return $validated;
    }

    /**
     * Build validation rules from field definitions
     */
    private function buildValidationRules(array $fields, array $data): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $key = $field['key'] ?? null;
            $fieldRules = $field['rules'] ?? [];
            $type = $field['type'] ?? 'text';

            if (!$key) {
                continue;
            }

            // Handle array fields
            if ($type === 'array' && isset($field['fields'])) {
                $rules[$key] = $fieldRules;
                
                // Add rules for nested fields
                foreach ($field['fields'] as $subField) {
                    $subKey = $subField['key'] ?? null;
                    $subRules = $subField['rules'] ?? [];
                    
                    if ($subKey) {
                        $rules["{$key}.*.{$subKey}"] = $subRules;
                    }
                }
            } else {
                // Handle simple fields
                $rules[$key] = $fieldRules;
            }
        }

        return $rules;
    }
}
