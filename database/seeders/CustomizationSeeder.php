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
                            'label' => 'Image (2304 x 4920 px)',
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
                [
                    'key' => 'testimonials',
                    'label' => 'Testimonials',
                    'type' => 'array',
                    'rules' => ['array'],
                    'items' => [
                        [
                            'key' => 'image',
                            'label' => 'Image (260 x 260 px)',
                            'type' => 'image',
                            'rules' => ['required', 'image', 'max:2048'],
                        ],
                        [
                            'key' => 'label',
                            'label' => 'Label',
                            'type' => 'text',
                            'rules' => ['required', 'string', 'max:100'],
                        ],
                        [
                            'key' => 'url',
                            'label' => 'URL',
                            'type' => 'text',
                            'rules' => ['nullable', 'string', 'url', 'max:1024'],
                        ]
                    ]
                ]
            ],
            'value' => [
                'carousel_items' => [],
                'testimonials' => [],
            ],
        ]);

        Customization::create([
            'key' => 'product_page',
            'label' => 'Product Page',
            'fields' => [
                [
                    'key' => 'accordion_items',
                    'label' => 'Accordion Items',
                    'type' => 'array',
                    'rules' => ['array'],
                    'items' => [
                        [
                            'key' => 'icon',
                            'label' => 'Icon',
                            'type' => 'text',
                            'rules' => ['required', 'string', 'max:50'],
                        ],
                        [
                            'key' => 'title',
                            'label' => 'Title',
                            'type' => 'text',
                            'rules' => ['required', 'string', 'max:100'],
                        ],
                        [
                            'key' => 'content',
                            'label' => 'Content',
                            'type' => 'text',
                            'rules' => ['required', 'string', 'max:1024'],
                        ],
                    ],
                ],
            ],
            'value' => [
                'accordion_items' => [],
            ],
        ]);

        Customization::create([
            'key' => 'footer',
            'label' => 'Footer',
            'fields' => [
                [
                    'key' => 'about',
                    'label' => 'About',
                    'type' => 'text',
                    'rules' => ['nullable', 'string', 'max:1024'],
                ],
            ],
            'value' => [
                'about' => '',
            ],
        ]);
    }
}
