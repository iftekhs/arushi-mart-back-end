<?php

namespace App\Http\Controllers\Api;

use App\Enums\CacheKey;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function show(string $key)
    {
        $data = Cache::tags(CacheKey::SETTING_SHOW->value)->remember("setting_{$key}", 60 * 60, function () use ($key) {
            $setting = Setting::where('key', $key)->first();
            
            if (!$setting) {
                return $this->getDefaultData($key);
            }

            return match ($key) {
                'scripts' => ['scripts' => $setting->value['scripts'] ?? []],
                'maintenance-mode' => ['maintenance_mode' => $setting->value['maintenance_mode'] ?? false],
                'shipping-fees' => [
                    'on_site_fee' => $setting->value['on_site_fee'] ?? 0,
                    'inside_dhaka_fee' => $setting->value['inside_dhaka_fee'] ?? 60,
                    'outside_dhaka_fee' => $setting->value['outside_dhaka_fee'] ?? 120,
                ],
                default => null,
            };
        });

        if ($data === null) {
            return response()->json(['error' => 'Invalid setting key'], 404);
        }

        return response()->json($data);
    }

    private function getDefaultData(string $key): ?array
    {
        return match ($key) {
            'scripts' => ['scripts' => []],
            'maintenance-mode' => ['maintenance_mode' => false],
            'shipping-fees' => [
                'on_site_fee' => 0,
                'inside_dhaka_fee' => 60,
                'outside_dhaka_fee' => 120,
            ],
            default => null,
        };
    }
}
