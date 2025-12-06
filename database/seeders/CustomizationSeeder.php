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
                            'key' => 'caption',
                            'label' => 'Caption',
                            'type' => 'text',
                            'rules' => ['required', 'string', 'max:255'],
                        ],
                        [
                            'key' => 'image',
                            'label' => 'Image',
                            'type' => 'image',
                            'rules' => ['required', 'image', 'max:2048'],
                        ],
                    ],
                ],
            ],
            'value' => [
                'carousel_items' => [
                    [
                        'caption' => 'Welcome to Arushi Mart',
                        'image' => '/storage/carousel/slide1.jpg',
                    ],
                    [
                        'caption' => 'New Collection 2024',
                        'image' => '/storage/carousel/slide2.jpg',
                    ],
                ],
            ],
        ]);
    }
}
