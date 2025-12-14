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
                        'key' => 'testimonials_title',
                        'label' => 'Testimonials Title',
                        'type' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
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
                    'testimonials_title' => '❤️ LOVED BY,',
                    'testimonials' => [],
                ],
            ],
            [
                'key' => 'featured_product_page',
                'label' => 'Featured Product Page',
                'fields' => [
                    [
                        'key' => 'label',
                        'label' => 'Label',
                        'type' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
                    ],
                    [
                        'key' => 'title',
                        'label' => 'Title',
                        'type' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
                    ],
                ],
                'value' => [
                    'label' => 'TRENDING',
                    'title' => 'THIS WEEK TOP 5',
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
                    [
                        'key' => 'link_sections',
                        'label' => 'Link Sections',
                        'type' => 'array',
                        'rules' => ['array'],
                        'items' => [
                            [
                                'key' => 'title',
                                'label' => 'Section Title',
                                'type' => 'text',
                                'rules' => ['required', 'string', 'max:100'],
                            ],
                            [
                                'key' => 'links',
                                'label' => 'Links',
                                'type' => 'array',
                                'rules' => ['array'],
                                'items' => [
                                    [
                                        'key' => 'label',
                                        'label' => 'Link Label',
                                        'type' => 'text',
                                        'rules' => ['required', 'string', 'max:100'],
                                    ],
                                    [
                                        'key' => 'url',
                                        'label' => 'Link URL',
                                        'type' => 'text',
                                        'rules' => ['required', 'string', 'max:1024'],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ],
                'value' => [
                    'about' => null,
                    'copyright' => '© 2025 - ArushiMart - Bangladesh',
                    'link_sections' => [
                        [
                            'title' => 'Shop',
                            'links' => [
                                ['label' => 'Women', 'url' => '/collections/women-1'],
                                ['label' => 'Men', 'url' => '/collections/men-1'],
                                ['label' => 'Small Leather Goods', 'url' => '/collections/small-leather-goods'],
                                ['label' => 'Collaboration', 'url' => '/collections/le-mini-dalia'],
                            ],
                        ],
                        [
                            'title' => 'Information',
                            'links' => [
                                ['label' => 'About us', 'url' => '/pages/about-us'],
                                ['label' => 'Contact us', 'url' => '/pages/contact'],
                                ['label' => 'FAQ', 'url' => '/pages/faq-new'],
                                ['label' => 'Privacy Policy', 'url' => '/privacy-policy'],
                                ['label' => 'Terms & Conditions', 'url' => '/terms-conditions'],
                            ],
                        ],
                    ],
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
            $existing = Customization::where('key', $customization['key'])->first();

            if ($existing) {
                // Update label and fields
                $existing->update([
                    'label' => $customization['label'],
                    'fields' => $customization['fields'],
                ]);

                // Merge values: only add new keys or update null/empty values
                $existingValue = is_array($existing->value) ? $existing->value : [];
                $newValue = $this->mergeValues($existingValue, $customization['value']);

                $existing->update(['value' => $newValue]);
            } else {
                // Create new customization
                Customization::create($customization);
            }
        }
    }

    private function mergeValues(array $existing, array $new): array
    {
        foreach ($new as $key => $value) {
            if (!array_key_exists($key, $existing)) {
                // Key doesn't exist, add it
                $existing[$key] = $value;
            } elseif ($this->isEmpty($existing[$key])) {
                // Key exists but is empty/null, update it
                $existing[$key] = $value;
            } elseif (is_array($value) && is_array($existing[$key]) && !$this->isSequentialArray($value)) {
                // Both are associative arrays, merge recursively
                $existing[$key] = $this->mergeValues($existing[$key], $value);
            }
            // Otherwise keep existing value (don't overwrite non-empty values)
        }

        return $existing;
    }

    private function isEmpty($value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        return false;
    }

    private function isSequentialArray(array $arr): bool
    {
        if (empty($arr)) {
            return true;
        }

        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
