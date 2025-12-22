<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        return SettingResource::collection(Setting::all());
    }

    public function show(Setting $setting)
    {
        return SettingResource::make($setting);
    }

    public function update(Request $request, string $path, SettingService $settingService)
    {
        $rules = $this->getValidationRules($path);

        $validated = $request->validate($rules);

        // Get current value for file handling
        $oldValue = $settingService->get($path) ?? [];

        // Process file uploads for seo.global
        if ($path === 'seo.global') {
            $validated = $this->processFileUploads($validated, $oldValue);
            $this->deleteOldImages($oldValue, $validated);
        }

        // Convert boolean fields for application settings
        if ($path === 'application') {
            $validated = $this->processApplicationSettings($validated);
        }

        $settingService->set($path, $validated);

        return $this->success($settingService->get($path));
    }

    private function processApplicationSettings(array $data): array
    {
        // Convert maintenance_mode to boolean
        if (isset($data['maintenance_mode'])) {
            $data['maintenance_mode'] = (bool) $data['maintenance_mode'];
        }

        return $data;
    }

    private function processFileUploads(array $data, array $oldValue = []): array
    {
        $imageFields = ['og_image', 'twitter_image', 'favicon', 'apple_icon'];

        foreach ($imageFields as $field) {
            $value = $data[$field] ?? null;

            if ($value instanceof UploadedFile) {
                // New upload - store the file
                $path = $value->store('settings/seo');
                $data[$field] = '/' . $path;
            } elseif ($value === '-1') {
                // Remove image (special value to indicate deletion)
                $data[$field] = null;
            } elseif (is_string($value) && $value !== '') {
                // Normalize full URLs to paths (frontend might send full URL)
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    // Extract path from full URL
                    $parsedUrl = parse_url($value);
                    $data[$field] = $parsedUrl['path'] ?? $value;
                } else {
                    // Already a path, keep as is
                    $data[$field] = $value;
                }
            } elseif ($value === null || !isset($data[$field])) {
                // Keep existing value if field not provided or is null
                $data[$field] = $oldValue[$field] ?? null;
            }
        }

        return $data;
    }

    private function deleteOldImages(array $oldValue, array $newValue): void
    {
        $imageFields = ['og_image', 'twitter_image', 'favicon', 'apple_icon'];

        foreach ($imageFields as $field) {
            $oldPath = $oldValue[$field] ?? null;
            $newPath = $newValue[$field] ?? null;

            // Delete old image if it was replaced with a new one
            if ($oldPath && $newPath && $oldPath !== $newPath && !filter_var($oldPath, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete(ltrim($oldPath, '/'));
            }
        }
    }

    private function getValidationRules(string $path): array
    {
        return match ($path) {
            'business' => [
                'on_site_fee' => ['required', 'numeric', 'min:0'],
                'inside_dhaka_fee' => ['required', 'numeric', 'min:0'],
                'outside_dhaka_fee' => ['required', 'numeric', 'min:0'],
            ],
            'application' => [
                'maintenance_mode' => ['required', 'boolean'],
                'scripts' => ['array'],
                'scripts.*' => ['required', 'string', 'max:2048'],
            ],
            'seo.global' => [
                // Essential
                'title' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:500'],
                'keywords' => ['nullable', 'array'],
                'keywords.*' => ['nullable', 'string', 'max:100'],

                // Open Graph
                'og_title' => ['nullable', 'string', 'max:255'],
                'og_description' => ['nullable', 'string', 'max:500'],
                'og_site_name' => ['nullable', 'string', 'max:255'],
                'og_image' => ['nullable', function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $validator = validator([$attribute => $value], [
                            $attribute => ['image', 'max:2048']
                        ]);
                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    } elseif (!is_string($value) && $value !== null) {
                        $fail('The ' . $attribute . ' must be an image or a valid path.');
                    }
                }],
                'og_image_width' => ['nullable', 'integer', 'min:1'],
                'og_image_height' => ['nullable', 'integer', 'min:1'],
                'og_image_alt' => ['nullable', 'string', 'max:255'],

                // Twitter
                'twitter_title' => ['nullable', 'string', 'max:255'],
                'twitter_description' => ['nullable', 'string', 'max:500'],
                'twitter_image' => ['nullable', function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $validator = validator([$attribute => $value], [
                            $attribute => ['image', 'max:2048']
                        ]);
                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    } elseif (!is_string($value) && $value !== null) {
                        $fail('The ' . $attribute . ' must be an image or a valid path.');
                    }
                }],
                'twitter_creator' => ['nullable', 'string', 'max:100'],

                // Branding
                'application_name' => ['nullable', 'string', 'max:255'],
                'favicon' => ['nullable', function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $validator = validator([$attribute => $value], [
                            $attribute => ['image', 'mimes:ico,png', 'max:1024']
                        ]);
                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    } elseif (!is_string($value) && $value !== null) {
                        $fail('The ' . $attribute . ' must be an image or a valid path.');
                    }
                }],
                'apple_icon' => ['nullable', function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $validator = validator([$attribute => $value], [
                            $attribute => ['image', 'max:1024']
                        ]);
                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    } elseif (!is_string($value) && $value !== null) {
                        $fail('The ' . $attribute . ' must be an image or a valid path.');
                    }
                }],
            ],
            default => [],
        };
    }
}
