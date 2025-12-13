<?php

namespace App\Http\Controllers\Api;

use App\Enums\SettingKey;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
  public function show(string $key): JsonResponse
  {
    $settingKey = $this->getSettingKey($key);

    if (!$settingKey) return $this->error('Invalid setting key', 404);

    $setting = cache()->get("settings.{$settingKey->value}");

    if (!$setting) return $this->error('Setting not found', 404);

    $data = $this->formatSettingData($key, (object)$setting->value);

    return $this->success($data);
  }

  public function showSecure(string $key): JsonResponse
  {
    $settingKey = $this->getSettingKey($key);

    if (!$settingKey) return $this->error('Invalid setting key', 404);

    $setting = cache()->get("settings.show.{$settingKey->value}");

    if (!$setting) return $this->error('Setting not found', 404);

    $data = $this->formatSecureSettingData($key, (object)$setting->value);

    return $this->success($data);
  }

  private function getSettingKey(string $key): ?SettingKey
  {
    return match ($key) {
      'shipping-fees' => SettingKey::BUSINESS,
      'scripts', 'maintenance_mode' => SettingKey::APPLICATION,
      default => null
    };
  }

  private function formatSettingData(string $key, object $value): ?array
  {
    return match ($key) {
      'shipping-fees' => [
        'on_site' => $value->on_site_fee ?? null,
        'inside_dhaka_fee' => $value->inside_dhaka_fee ?? null,
        'outside_dhaka_fee' => $value->outside_dhaka_fee ?? null,
      ],
      default => null
    };
  }

  private function formatSecureSettingData(string $key, object $value): mixed
  {
    return match ($key) {
      'scripts' => $value->scripts ?? null,
      'maintenance_mode' => $value->maintenance_mode ?? null,
      default => null
    };
  }
}
