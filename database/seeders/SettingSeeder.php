<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultValue = [
            'business' => [
                'on_site_fee' => 0,
                'inside_dhaka_fee' => 60,
                'outside_dhaka_fee' => 120,
            ],
            'application' => [
                'maintenance_mode' => false,
                'scripts' => [],
            ],
            'seo' => [
                'global' => [
                    // Essential
                    'title' => 'Arushi Mart',
                    'description' => 'Your one-stop shop for trendy fashion and unbeatable deals.',
                    'keywords' => ['fashion', 'shopping', 'online store', 'trendy clothing', 'deals'],
                    
                    // Open Graph
                    'og_title' => 'Arushi Mart - Trendy Fashion & Unbeatable Deals',
                    'og_description' => 'Your one-stop shop for trendy fashion and unbeatable deals.',
                    'og_site_name' => 'Arushi Mart',
                    'og_image' => null,
                    'og_image_width' => 1200,
                    'og_image_height' => 630,
                    'og_image_alt' => 'Arushi Mart',
                    
                    // Twitter
                    'twitter_title' => 'Arushi Mart - Trendy Fashion & Unbeatable Deals',
                    'twitter_description' => 'Your one-stop shop for trendy fashion and unbeatable deals.',
                    'twitter_image' => null,
                    'twitter_creator' => '@arushimart',
                    
                    // Branding
                    'application_name' => 'Arushi Mart',
                    'favicon' => null,
                    'apple_icon' => null,
                ],
            ],
        ];

        $existing = Setting::where('key', 'app_settings')->first();

        if ($existing) {
            // Merge values: only add new keys or update null/empty values
            $existingValue = is_array($existing->value) ? $existing->value : [];
            $newValue = $this->mergeValues($existingValue, $defaultValue);

            $existing->update(['value' => $newValue]);
        } else {
            // Create new setting
            Setting::create([
                'key' => 'app_settings',
                'value' => $defaultValue,
            ]);
        }
    }

    private function mergeValues(array $existing, array $new): array
    {
        foreach ($new as $key => $value) {
            if (!array_key_exists($key, $existing)) {
                // Key doesn't exist, add it
                $existing[$key] = $value;
            } elseif ($this->isEmpty($existing[$key])) {
                // Key exists but is empty/null, update it
                $existing[$key] = $value;
            } elseif (is_array($value) && is_array($existing[$key]) && !$this->isSequentialArray($value)) {
                // Both are associative arrays, merge recursively
                $existing[$key] = $this->mergeValues($existing[$key], $value);
            }
            // Otherwise keep existing value (don't overwrite non-empty values)
        }

        return $existing;
    }

    private function isEmpty($value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        return false;
    }

    private function isSequentialArray(array $arr): bool
    {
        if (empty($arr)) {
            return true;
        }

        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
