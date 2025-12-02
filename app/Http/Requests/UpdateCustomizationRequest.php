<?php

namespace App\Http\Requests;

use App\Models\Customization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
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
            'id' => ['required', 'integer', 'exists:customizations,id'],
            'data' => ['nullable', 'array'],
        ];
    }

    /**
     * Validate the data against field definitions from database
     */
    public function validateWithFieldDefinitions(): array
    {
        $customizationId = $this->input('id');
        $customization = Customization::find($customizationId);

        if (!$customization) {
            throw ValidationException::withMessages([
                'id' => "Customization not found."
            ]);
        }

        $fields = $customization->fields ?? [];
        $data = $this->input('data', []);

        // Merge files into data structure
        $data = $this->mergeFilesIntoData($data);

        // Build dynamic validation rules from field definitions
        $rules = $this->buildValidationRules($fields, $data);

        // Validate the data
        $validator = validator($data, $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'data' => $validator->errors()->all()
            ]);
        }

        // Process file uploads and handle old file deletion
        $processedData = $this->processFileUploads($validator->validated(), $fields, $customization->value);

        return [
            'id' => $customizationId,
            'data' => $processedData,
        ];
    }

    /**
     * Merge uploaded files into the data structure
     */
    private function mergeFilesIntoData(array $data): array
    {
        $allFiles = $this->allFiles();

        // First, convert string 'null' to actual null
        $data = $this->convertNullStrings($data);

        // Check for files in the format: data[field][subindex][subfield]
        foreach ($allFiles as $key => $file) {
            // Parse the key to extract the structure
            // Example: "data[carousel_items][0][image]" or "data[image]"
            if (preg_match("/^data\[([^\]]+)\](?:\[(\d+)\]\[([^\]]+)\])?$/", $key, $matches)) {
                if (isset($matches[3])) {
                    // Array field with subfield: carousel_items[0][image]
                    $fieldKey = $matches[1];
                    $arrayIndex = $matches[2];
                    $subFieldKey = $matches[3];

                    if (!isset($data[$fieldKey])) {
                        $data[$fieldKey] = [];
                    }
                    if (!isset($data[$fieldKey][$arrayIndex])) {
                        $data[$fieldKey][$arrayIndex] = [];
                    }
                    $data[$fieldKey][$arrayIndex][$subFieldKey] = $file;
                } else {
                    // Simple field: image
                    $fieldKey = $matches[1];
                    $data[$fieldKey] = $file;
                }
            }
        }

        return $data;
    }

    /**
     * Convert string 'null' to actual null values recursively
     */
    private function convertNullStrings(array $data): array
    {
        foreach ($data as $key => &$value) {
            if ($value === 'null') {
                $value = null;
            } elseif (is_array($value)) {
                $value = $this->convertNullStrings($value);
            }
        }
        return $data;
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
                // Make array fields nullable to allow empty arrays
                $rules[$key] = ['nullable', 'array'];

                // Add rules for nested fields
                foreach ($field['fields'] as $subField) {
                    $subKey = $subField['key'] ?? null;
                    $subRules = $subField['rules'] ?? [];
                    $subType = $subField['type'] ?? 'text';

                    if ($subKey) {
                        // For image fields in arrays, make them nullable if updating
                        if ($subType === 'image') {
                            $modifiedRules = array_map(function ($rule) {
                                return $rule === 'required' ? 'nullable' : $rule;
                            }, $subRules);
                            $rules["{$key}.*.{$subKey}"] = $modifiedRules;
                        } else {
                            $rules["{$key}.*.{$subKey}"] = $subRules;
                        }
                    }
                }
            } else {
                // For image fields, make them nullable if updating
                if ($type === 'image') {
                    $modifiedRules = array_map(function ($rule) {
                        return $rule === 'required' ? 'nullable' : $rule;
                    }, $fieldRules);
                    $rules[$key] = $modifiedRules;
                } else {
                    $rules[$key] = $fieldRules;
                }
            }
        }

        return $rules;
    }

    /**
     * Process file uploads and handle old file deletion
     */
    private function processFileUploads(array $data, array $fields, ?array $oldValue): array
    {
        foreach ($fields as $field) {
            $key = $field['key'] ?? null;
            $type = $field['type'] ?? 'text';

            if (!$key) {
                continue;
            }

            // Handle array fields with image subfields
            if ($type === 'array' && isset($field['fields'])) {
                // First, delete images from removed array items
                if (isset($oldValue[$key]) && is_array($oldValue[$key])) {
                    $newItemCount = isset($data[$key]) ? count($data[$key]) : 0;
                    $oldItemCount = count($oldValue[$key]);
                    
                    // If items were removed, delete their images
                    if ($newItemCount < $oldItemCount) {
                        for ($i = $newItemCount; $i < $oldItemCount; $i++) {
                            if (isset($oldValue[$key][$i])) {
                                // Find image fields and delete them
                                foreach ($field['fields'] as $subField) {
                                    $subKey = $subField['key'] ?? null;
                                    $subType = $subField['type'] ?? 'text';
                                    
                                    if ($subKey && $subType === 'image' && isset($oldValue[$key][$i][$subKey])) {
                                        Storage::disk('public')->delete($oldValue[$key][$i][$subKey]);
                                    }
                                }
                            }
                        }
                    }
                }

                // Process existing items
                if (isset($data[$key])) {
                    foreach ($data[$key] as $index => &$item) {
                        foreach ($field['fields'] as $subField) {
                            $subKey = $subField['key'] ?? null;
                            $subType = $subField['type'] ?? 'text';

                            if ($subKey && $subType === 'image') {
                                // Check if value is explicitly null (image removed)
                                if (array_key_exists($subKey, $item) && $item[$subKey] === null) {
                                    // Delete old file if exists
                                    if (isset($oldValue[$key][$index][$subKey])) {
                                        Storage::disk('public')->delete($oldValue[$key][$index][$subKey]);
                                    }
                                    // Keep the null value to indicate removal
                                    $item[$subKey] = null;
                                }
                                // Check if the value is an UploadedFile instance (new upload)
                                elseif (isset($item[$subKey]) && $item[$subKey] instanceof \Illuminate\Http\UploadedFile) {
                                    $file = $item[$subKey];

                                    // Delete old file if exists
                                    if (isset($oldValue[$key][$index][$subKey])) {
                                        Storage::disk('public')->delete($oldValue[$key][$index][$subKey]);
                                    }

                                    // Store new file
                                    $path = $file->store('customizations', 'public');
                                    $item[$subKey] = $path;
                                } else {
                                    // Keep old value if no new file uploaded and not removed
                                    if (isset($oldValue[$key][$index][$subKey])) {
                                        $item[$subKey] = $oldValue[$key][$index][$subKey];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // Handle simple image fields
            elseif ($type === 'image') {
                // Check if value is explicitly null (image removed)
                if (array_key_exists($key, $data) && $data[$key] === null) {
                    // Delete old file if exists
                    if (isset($oldValue[$key])) {
                        Storage::disk('public')->delete($oldValue[$key]);
                    }
                    // Keep the null value to indicate removal
                    $data[$key] = null;
                }
                // Check if the value is an UploadedFile instance
                elseif (isset($data[$key]) && $data[$key] instanceof \Illuminate\Http\UploadedFile) {
                    $file = $data[$key];

                    // Delete old file if exists
                    if (isset($oldValue[$key])) {
                        Storage::disk('public')->delete($oldValue[$key]);
                    }

                    // Store new file
                    $path = $file->store('customizations', 'public');
                    $data[$key] = $path;
                } else {
                    // Keep old value if no new file uploaded and not removed
                    if (isset($oldValue[$key])) {
                        $data[$key] = $oldValue[$key];
                    }
                }
            }
        }

        return $data;
    }
}
