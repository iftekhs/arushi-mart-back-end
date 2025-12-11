<?php

namespace App\Http\Requests;

use App\Models\Customization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
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
            'data' => ['nullable', 'array'],
        ];
    }

    /**
     * Validate the data against field definitions from database
     */
    public function validateWithFieldDefinitions(): array
    {
        $customization = $this->route('customization');

        $data = $this->mergeFilesIntoData($this->input('data', []));

        $fields = $customization->fields ?? [];

        $rules = $this->buildValidationRules($fields, $data);

        $validator = validator($data, $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'data' => $validator->errors()->all()
            ]);
        }

        logger('data', [
            'data' => $this->mergeFilesIntoData($this->input('data', [])),
        ]);
        dd('yes');

        $processedData = $this->processFileUploads($validator->validated(), $fields, $customization->value);

        return [

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

    private function processFileUploads(array $data, array $fields, ?array $oldValue): array
    {
        $data = $this->processFields($data, $fields, $oldValue);

        logger('data', [
            'data' => $data,
        ]);
        dd('yes');

        return $data;
    }

    private function processFields(array $data, array $fields, ?array $oldValue): array
    {
        foreach ($fields as $field) {
            $key = $field['key'] ?? null;
            $type = $field['type'] ?? 'text';

            if (!$key) continue;

            if ($type === 'array' && isset($field['fields'])) {
                $data[$key] = $this->processArrayField(
                    $data[$key] ?? [],
                    $field['fields'],
                    $oldValue[$key] ?? []
                );
            } elseif ($type === 'image') {
                $data[$key] = $this->processImageField(
                    $data[$key] ?? null,
                    $oldValue[$key] ?? null
                );
            }
        }

        return $data;
    }

    private function processArrayField(array $data, array $subFields, array $oldValue): array
    {
        // Delete images from removed array items
        $this->deleteRemovedArrayItems($data, $subFields, $oldValue);

        // Process each item recursively
        foreach ($data as $index => &$item) {
            if (is_array($item)) {
                $item = $this->processFields(
                    $item,
                    $subFields,
                    $oldValue[$index] ?? []
                );
            }
        }

        return $data;
    }

    private function deleteRemovedArrayItems(array $newData, array $fields, array $oldData): void
    {
        $newItemCount = count($newData);
        $oldItemCount = count($oldData);

        if ($newItemCount >= $oldItemCount) {
            return;
        }

        // Delete images from removed items
        for ($i = $newItemCount; $i < $oldItemCount; $i++) {
            if (!isset($oldData[$i])) {
                continue;
            }

            $this->deleteImagesFromItem($oldData[$i], $fields);
        }
    }

    private function deleteImagesFromItem(array $item, array $fields): void
    {
        foreach ($fields as $field) {
            $key = $field['key'] ?? null;
            $type = $field['type'] ?? 'text';

            if (!$key || !isset($item[$key])) {
                continue;
            }

            if ($type === 'image') {
                Storage::delete($item[$key]);
            } elseif ($type === 'array' && isset($field['fields']) && is_array($item[$key])) {
                // Recursively delete images from nested arrays
                foreach ($item[$key] as $nestedItem) {
                    if (is_array($nestedItem)) {
                        $this->deleteImagesFromItem($nestedItem, $field['fields']);
                    }
                }
            }
        }
    }

    private function processImageField($newValue, ?string $oldValue): ?string
    {
        // Image explicitly removed
        if ($newValue === null) {
            if ($oldValue) {
                Storage::delete($oldValue);
            }
            return null;
        }

        // New image uploaded
        if ($newValue instanceof UploadedFile) {
            if ($oldValue) {
                Storage::delete($oldValue);
            }
            return $newValue->store('customizations');
        }

        // No change - keep old value
        return $oldValue;
    }
}
