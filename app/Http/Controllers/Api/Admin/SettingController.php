<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Services\SettingService;
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

    public function update(Request $request, string $path, SettingService $settingService)
    {
        $rules = $this->getValidationRules($path);

        $validated = $request->validate($rules);

        $settingService->set($path, $validated);

        return $this->success($settingService->get($path));
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
            default => [],
        };
    }
}
