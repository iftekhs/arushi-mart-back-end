<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
  public function __construct(
    private SettingService $settingService
  ) {}

  public function show(string $key): JsonResponse
  {
    $data = $this->formatSettingData($key);

    if ($data === null) {
      return $this->error('Setting not found', 404);
    }

    return $this->success($data);
  }

  public function showSecure(string $key): JsonResponse
  {
    $data = $this->formatSecureSettingData($key);

    if ($data === null) {
      return $this->error('Setting not found', 404);
    }

    return $this->success($data);
  }

  private function formatSettingData(string $key): ?array
  {
    return match ($key) {
      'shipping-fees' => [
        'on_site_fee' => $this->settingService->get('business.on_site_fee'),
        'inside_dhaka_fee' => $this->settingService->get('business.inside_dhaka_fee'),
        'outside_dhaka_fee' => $this->settingService->get('business.outside_dhaka_fee'),
      ],
      default => null
    };
  }

  private function formatSecureSettingData(string $key): mixed
  {
    return match ($key) {
      'scripts' => $this->settingService->get('application.scripts'),
      'maintenance-mode' => $this->settingService->get('application.maintenance_mode'),
      'seo-global' => $this->transformSeoImagePaths($this->settingService->get('seo.global')),
      default => null
    };
  }

  private function transformSeoImagePaths(?array $data): ?array
  {
    if (!$data) return $data;

    $imageFields = ['og_image', 'twitter_image', 'favicon', 'apple_icon'];
    
    foreach ($imageFields as $field) {
      if (isset($data[$field])) {
        $data[$field] = path_to_url($data[$field]);
      }
    }

    return $data;
  }
}
