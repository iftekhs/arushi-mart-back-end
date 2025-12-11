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
        $settings = [
            [
                'key' => 'business',
                'value' => [
                    'on_site_fee' => 0,
                    'inside_dhaka_fee' => 60,
                    'outside_dhaka_fee' => 120,
                ],
            ],
            [
                'key' => 'application',
                'value' => [
                    'maintenance_mode' => false,
                    'scripts' => [],
                ],
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
