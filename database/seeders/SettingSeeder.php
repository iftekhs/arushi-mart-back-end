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
        Setting::updateOrCreate(
            ['key' => 'app_settings'],
            [
                'value' => [
                    'business' => [
                        'on_site_fee' => 0,
                        'inside_dhaka_fee' => 60,
                        'outside_dhaka_fee' => 120,
                    ],
                    'application' => [
                        'maintenance_mode' => false,
                        'scripts' => [],
                    ],
                ],
            ]
        );
    }
}
