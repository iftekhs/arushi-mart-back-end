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
                ],
            ]
        );
    }
}
