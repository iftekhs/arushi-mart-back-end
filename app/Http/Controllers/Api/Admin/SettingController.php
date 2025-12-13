<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\SettingKey;
use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;

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

        cache()->forget("settings.{$setting->key->value}");
        cache()->put("settings.{$setting->key->value}", $setting, 60 * 60);

        return SettingResource::make($setting);
    }

    private function getValidationRules(SettingKey $key): array
    {
        return match ($key) {
            SettingKey::BUSINESS => [
                'on_site_fee' => ['required', 'numeric', 'min:0'],
                'inside_dhaka_fee' => ['required', 'numeric', 'min:0'],
                'outside_dhaka_fee' => ['required', 'numeric', 'min:0'],
            ],
            SettingKey::APPLICATION => [
                'maintenance_mode' => ['required', 'boolean'],
                'scripts' => ['array'],
                'scripts.*' => ['required', 'string'],
            ],
            default => [],
        };
    }
}
