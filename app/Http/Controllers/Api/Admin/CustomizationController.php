<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\CacheKey;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomizationResource;
use App\Models\Customization;
use App\Services\CustomizationService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CustomizationController extends Controller
{
    public function index()
    {
        return CustomizationResource::collection(Customization::all());
    }

    public function show(Customization $customization)
    {
        return CustomizationResource::make($customization);
    }

    public function update(Request $request, Customization $customization, CustomizationService $service)
    {
        // Validate uploaded files based on customization's fields schema
        $validated = $service->validateCustomization(
            $customization,
            $request->all()
        );

        // Process uploaded files and convert to paths
        $processedData = $this->processFileUploads($validated, $customization->fields ?? [], $customization->value ?? []);

        // Delete old images if they were replaced
        $this->deleteOldImages($customization->value ?? [], $processedData, $customization->fields ?? []);

        // Update customization value
        $customization->update([
            'value' => $processedData,
        ]);

        Cache::tags(CacheKey::CUSTOMIZATION_SHOW->value)->flush();

        return CustomizationResource::make($customization);
    }

    private function processFileUploads(array $data, array $fields, array $oldValue = []): array
    {
        foreach ($fields as $field) {
            $key = $field['key'] ?? null;
            if (!$key) continue;

            if ($field['type'] === 'array' && isset($field['items'])) {
                // Process array fields
                if (isset($data[$key]) && is_array($data[$key])) {
                    foreach ($data[$key] as $index => $item) {
                        foreach ($field['items'] as $itemField) {
                            $itemKey = $itemField['key'] ?? null;
                            if (!$itemKey) continue;

                            if ($itemField['type'] === 'image') {
                                $value = $item[$itemKey] ?? null;

                                if ($value instanceof UploadedFile) {
                                    // New upload - store the file
                                    $path = $value->store('customizations');
                                    $data[$key][$index][$itemKey] = '/' . $path;
                                } elseif ($value === '-1') {
                                    // Remove image
                                    $data[$key][$index][$itemKey] = null;
                                } elseif ($value === null || !isset($item[$itemKey])) {
                                    // Keep existing value
                                    $data[$key][$index][$itemKey] = $oldValue[$key][$index][$itemKey] ?? null;
                                }
                            }
                        }
                    }
                }
            } elseif ($field['type'] === 'image') {
                $value = $data[$key] ?? null;

                if ($value instanceof UploadedFile) {
                    // New upload - store the file
                    $path = $value->store('customizations');
                    $data[$key] = '/' . $path;
                } elseif ($value === '-1') {
                    // Remove image
                    $data[$key] = null;
                } elseif ($value === null || !isset($data[$key])) {
                    // Keep existing value
                    $data[$key] = $oldValue[$key] ?? null;
                }
            }
        }

        return $data;
    }

    private function deleteOldImages(array $oldValue, array $newValue, array $fields): void
    {
        foreach ($fields as $field) {
            $key = $field['key'] ?? null;
            if (!$key) continue;

            if ($field['type'] === 'array' && isset($field['items'])) {
                $oldItems = $oldValue[$key] ?? [];
                $newItems = $newValue[$key] ?? [];

                // Delete images from removed items
                for ($i = count($newItems); $i < count($oldItems); $i++) {
                    foreach ($field['items'] as $itemField) {
                        $itemKey = $itemField['key'] ?? null;
                        if ($itemKey && $itemField['type'] === 'image' && isset($oldItems[$i][$itemKey])) {
                            Storage::delete(ltrim($oldItems[$i][$itemKey], '/'));
                        }
                    }
                }

                // Delete replaced images
                foreach ($newItems as $index => $newItem) {
                    foreach ($field['items'] as $itemField) {
                        $itemKey = $itemField['key'] ?? null;
                        if ($itemKey && $itemField['type'] === 'image') {
                            $oldPath = $oldItems[$index][$itemKey] ?? null;
                            $newPath = $newItem[$itemKey] ?? null;

                            if ($oldPath && $newPath && $oldPath !== $newPath) {
                                Storage::delete(ltrim($oldPath, '/'));
                            }
                        }
                    }
                }
            } elseif ($field['type'] === 'image') {
                $oldPath = $oldValue[$key] ?? null;
                $newPath = $newValue[$key] ?? null;

                if ($oldPath && $newPath && $oldPath !== $newPath) {
                    Storage::delete(ltrim($oldPath, '/'));
                }
            }
        }
    }
}
