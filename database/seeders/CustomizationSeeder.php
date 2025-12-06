<?php

namespace Database\Seeders;

use App\Models\Customization;
use Illuminate\Database\Seeder;

class CustomizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customization::create([
            'key' => 'home_page',
            'label' => 'Home Page',
            'fields' => [
                [
                    'key' => 'carousel_items',
                    'label' => 'Carousel Items',
                    'type' => 'array',
                    'rules' => ['array'],
                    'items' => [
                        [
                            'key' => 'image',
                            'label' => 'Image',
                            'type' => 'image',
                            'rules' => ['required', 'image', 'max:2048'],
                        ],
                        [
                            'key' => 'title',
                            'label' => 'Title',
                            'type' => 'text',
                            'rules' => ['required', 'string', 'max:100'],
                        ],
                        [
                            'key' => 'button_text',
                            'label' => 'Button Text',
                            'type' => 'text',
                            'rules' => ['required', 'string', 'max:50'],
                        ],
                        [
                            'key' => 'button_url',
                            'label' => 'Button URL',
                            'type' => 'text',
                            'rules' => ['required', 'string', 'url', 'max:1024'],
                        ],
                    ],
                ],
            ],
            'value' => [
                'carousel_items' => [],
            ],
        ]);
    }
}
