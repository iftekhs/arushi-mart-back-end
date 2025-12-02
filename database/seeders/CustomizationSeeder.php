<?php

namespace Database\Seeders;

use App\Models\Customization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customization::create([
            'key' => 'carousel_items',
            'label' => 'Carousel Items',
            'fields' => [
                [
                    'label' => 'Carousel Items',
                    'key' => 'carousel_items',
                    'type' => 'array',
                    'rules' => ['required', 'array', 'min:1'],
                    'fields' => [
                        [
                            'label' => 'Image',
                            'key' => 'image',
                            'type' => 'image',
                            'rules' => ['required', 'image', 'max:1024'],
                        ],
                        [
                            'label' => 'Caption',
                            'key' => 'caption',
                            'type' => 'text',
                            'rules' => ['required', 'string', 'max:255'],
                        ],
                        [
                            'label' => 'Button Text',
                            'key' => 'button_text',
                            'type' => 'text',
                            'rules' => ['required', 'string', 'max:255'],
                        ],
                        [
                            'label' => 'Button URL',
                            'key' => 'button_url',
                            'type' => 'text',
                            'rules' => ['required', 'url', 'max:255'],
                        ],
                    ],
                ],
            ],
            'value' => null,
        ]);

        Customization::create([
            'key' => 'about_section',
            'label' => 'About Section',
            'fields' => [
                [
                    'label' => 'Title',
                    'key' => 'title',
                    'type' => 'text',
                    'rules' => ['required', 'string', 'max:255'],
                ],
                [
                    'label' => 'Description',
                    'key' => 'description',
                    'type' => 'text',
                    'rules' => ['required', 'string', 'max:255'],
                ],
            ],
            'value' => null,
        ]);
    }
}
