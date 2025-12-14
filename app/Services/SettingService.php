<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Arr;

class SettingService
{
    private const CACHE_KEY = 'app_settings';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a setting value by dot notation path
     * 
     * @param string $path Dot notation path (e.g., 'business.on_site_fee')
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $path, $default = null)
    {
        $settings = $this->getAllSettings();
        return Arr::get($settings, $path, $default);
    }

    /**
     * Set a setting value by dot notation path
     * 
     * @param string $path Dot notation path
     * @param mixed $value Value to set
     * @return void
     */
    public function set(string $path, $value): void
    {
        $setting = Setting::firstOrCreate(['key' => self::CACHE_KEY]);
        
        $settings = $setting->value ?? [];
        Arr::set($settings, $path, $value);
        
        $setting->update(['value' => $settings]);
        
        $this->refreshCache();
    }

    /**
     * Get all settings
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->getAllSettings();
    }

    /**
     * Forget a specific setting path from cache and database
     * 
     * @param string $path Dot notation path
     * @return void
     */
    public function forget(string $path): void
    {
        $setting = Setting::where('key', self::CACHE_KEY)->first();
        
        if ($setting) {
            $settings = $setting->value ?? [];
            Arr::forget($settings, $path);
            $setting->update(['value' => $settings]);
        }
        
        $this->refreshCache();
    }

    /**
     * Refresh the settings cache
     * 
     * @return void
     */
    public function refreshCache(): void
    {
        cache()->forget(self::CACHE_KEY);
        $this->getAllSettings();
    }

    /**
     * Get all settings from cache or database
     * 
     * @return array
     */
    private function getAllSettings(): array
    {
        return cache()->remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $setting = Setting::where('key', self::CACHE_KEY)->first();
            return $setting?->value ?? [];
        });
    }
}
