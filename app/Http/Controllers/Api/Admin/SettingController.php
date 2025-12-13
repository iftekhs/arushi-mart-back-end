<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\CacheKey;
use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

    public function update(Request $request, Setting $setting)
    {
        $rules = $this->getValidationRules($setting->key);
        
        $validated = $request->validate($rules);

        $setting->update([
            'value' => $validated,
        ]);

        // Flush settings cache
        Cache::tags(CacheKey::SETTING_SHOW->value)->flush();

        return SettingResource::make($setting);
    }

    private function getValidationRules(string $key): array
    {
        return match ($key) {
            'business' => [
                'on_site_fee' => ['required', 'numeric', 'min:0'],
                'inside_dhaka_fee' => ['required', 'numeric', 'min:0'],
                'outside_dhaka_fee' => ['required', 'numeric', 'min:0'],
            ],
            'application' => [
                'maintenance_mode' => ['required', 'boolean'],
                'scripts' => ['array'],
                'scripts.*' => ['required', 'string'],
            ],
            default => [],
        };
    }
}
