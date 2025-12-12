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
        $customizations = [
            [
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
            ],
            [
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
                                'type' => 'markdown',
                                'rules' => ['required', 'string', 'max:5120'],
                            ],
                        ],
                    ],
                ],
                'value' => [
                    'accordion_items' => [],
                ],
            ],
            [
                'key' => 'about_page',
                'label' => 'About Page',
                'fields' => [
                    [
                        'key' => 'title',
                        'label' => 'Title',
                        'type' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
                    ],
                    [
                        'key' => 'banner',
                        'label' => 'Banner (1900 x 560 px)',
                        'type' => 'image',
                        'rules' => ['nullable', 'image', 'max:2048'],
                    ],
                    [
                        'key' => 'images',
                        'label' => 'Images',
                        'type' => 'array',
                        'rules' => ['array'],
                        'items' => [
                            [
                                'key' => 'file',
                                'label' => 'File (350 x 320 px)',
                                'type' => 'image',
                                'rules' => ['nullable', 'image', 'max:2048'],
                            ],
                        ]
                    ],
                    [
                        'key' => 'content',
                        'label' => 'Content',
                        'type' => 'markdown',
                        'rules' => ['nullable', 'string', 'max:2048'],
                    ],
                    [
                        'key' => 'block_title',
                        'label' => 'Block Title',
                        'type' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
                    ],
                    [
                        'key' => 'block_content',
                        'label' => 'Block Content',
                        'type' => 'markdown',
                        'rules' => ['nullable', 'string', 'max:1024'],
                    ],
                ],
                'value' => [
                    'title' => 'OUR INSPIRING JOURNEY',
                    'banner' => null,
                    'images' => [],
                    'content' => 'Mlouye\'s founder and creative director Meb Rure hails from an
                        industrial design background. In 2015, Meb decided to change gears
                        and turn her energy towards Mlouye, a collection of exceptional
                        handbags. Focusing on quality material, good design, craftsmanship
                        and sustainability, Mlouye reflects the epitome of quality over
                        quantity.
                        
                        Meb was highly inspired by the Bauhaus Movement\'s artists and
                        architects. From Mies van der Rohe\'s works to Kandinsky\'s
                        paintings, to Aalto\'s furniture, she acquired a rationalist vision
                        of design by gleaning how they served a utilitarian purpose in a
                        cleverly simple way. Mlouye merges industrial design and fashion,
                        creating functional handbags made of luxurious and honest
                        materials to improve people\'s lives in small but important ways.
                        
                        
                        Innovation being the key factor alongside aesthetic, Mlouye has
                        brought unexpected shapes with smart details, functionality and a
                        new luxury feel with a contemporary price point.
                        
                        Istanbul is where Mlouye was born, the company\'s headquarters is
                        now split between the US, and Turkey.',
                    'block_title' => 'Our quality promise',
                    'block_content' => 'Quality is never an accident. It is always the result of intelligent
                        effort. We spend most of our time and energy for good design
                        and to achieve high quality. Every single detail from material to
                        technique is thought through  with obsessive attention. If our
                        product doesn\'t satisfy you, we\'ll take it back.',
                ],
            ],
            [
                'key' => 'footer',
                'label' => 'Footer Component',
                'fields' => [
                    [
                        'key' => 'about',
                        'label' => 'About',
                        'type' => 'markdown',
                        'rules' => ['nullable', 'string', 'max:1024'],
                    ],
                    [
                        'key' => 'copyright',
                        'label' => 'Copyright',
                        'type' => 'text',
                        'rules' => ['nullable', 'string', 'max:255'],
                    ],
                    [
                        'key' => 'social_links',
                        'label' => 'Social Links',
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
                                'key' => 'url',
                                'label' => 'URL',
                                'type' => 'text',
                                'rules' => ['required', 'string', 'url', 'max:1024'],
                            ],
                        ]
                    ],
                ],
                'value' => [
                    'about' => null,
                    'copyright' => '© 2025 - ArushiMart - Bangladesh',
                    'social_links' => []
                ],
            ],
            [
                'key' => 'auth',
                'label' => 'Authentication Page',
                'fields' => [
                    [
                        'key' => 'background_image',
                        'label' => 'Background Image',
                        'type' => 'image',
                        'rules' => ['nullable', 'image', 'max:2048'],
                    ],
                ],
                'value' => [
                    'background_image' => null,
                ],
            ],
        ];

        foreach ($customizations as $customization) {
            Customization::updateOrCreate(
                ['key' => $customization['key']],
                $customization
            );
        }
    }
}
