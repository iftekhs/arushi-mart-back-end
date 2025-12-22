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
                'key' => 'top_marquee',
                'label' => 'Top Marquee',
                'fields' => [
                    [
                        'key' => 'announcements',
                        'label' => 'Announcements',
                        'type' => 'array',
                        'rules' => ['array'],
                        'items' => [
                            [
                                'key' => 'text',
                                'label' => 'Announcement Text',
                                'type' => 'text',
                                'rules' => ['required', 'string', 'max:200'],
                            ],
                        ],
                    ],
                ],
                'value' => [
                    'announcements' => [
                        ['text' => 'Spring Sale: Up to 30% off selected items!'],
                        ['text' => 'Free shipping on orders over ₹999 — limited time.'],
                        ['text' => 'New arrivals: Fresh styles added daily.'],
                        ['text' => 'Subscribe for exclusive deals and early access.'],
                        ['text' => 'Easy returns within 15 days — no questions asked.'],
                        ['text' => 'Earn rewards on every purchase — join our loyalty program!'],
                        ['text' => 'Secure payments and 24/7 customer support.'],
                        ['text' => 'Limited stock — grab your favorites before they\'re gone!'],
                    ],
                ],
            ],
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
                                'key' => 'small_image',
                                'label' => 'Small Image (Desktop - 1920 x 1080 px)',
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
                'key' => 'privacy_policy_page',
                'label' => 'Privacy Policy Page',
                'fields' => [
                    [
                        'key' => 'title',
                        'label' => 'Page Title',
                        'type' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
                    ],
                    [
                        'key' => 'last_updated',
                        'label' => 'Last Updated',
                        'type' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
                    ],
                    [
                        'key' => 'content',
                        'label' => 'Privacy Policy Content',
                        'type' => 'markdown',
                        'rules' => ['nullable', 'string', 'max:10240'],
                    ],
                    [
                        'key' => 'sidebar_image',
                        'label' => 'Sidebar Image (Optional)',
                        'type' => 'image',
                        'rules' => ['nullable', 'image', 'max:2048'],
                    ],
                ],
                'value' => [
                    'title' => 'Privacy Policy',
                    'last_updated' => 'December 2025',
                    'content' => "Your privacy is important to us. It is ArushiMart's policy to respect your privacy regarding any information we may collect from you across our website.

## Information We Collect

We only collect information about you if we have a reason to do so – for example, to provide our Services, to communicate with you, or to make our Services better.

### Personal Information

We collect personal information that you provide to us when you use our Services, such as your name, email address, phone number, shipping address, and any other contact information you provide.

### Usage Data

We collect information about your interactions with our Services, such as the pages you visit, the links you click, and the search terms you use.

## How We Use Information

We use the information we collect in various ways, including to:

- Provide, operate, and maintain our website
- Improve, personalize, and expand our website
- Understand and analyze how you use our website
- Develop new products, services, features, and functionality
- Communicate with you for customer service, updates, and marketing purposes
- Process your transactions and manage your orders

## Data Security

We implement appropriate security measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.

## Contact

If you have any questions about how we handle your data, please contact us at privacy@arushimart.com.",
                    'sidebar_image' => null,
                ],
            ],
            [
                'key' => 'terms_conditions_page',
                'label' => 'Terms & Conditions Page',
                'fields' => [
                    [
                        'key' => 'title',
                        'label' => 'Page Title',
                        'type' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
                    ],
                    [
                        'key' => 'last_updated',
                        'label' => 'Last Updated',
                        'type' => 'text',
                        'rules' => ['nullable', 'string', 'max:100'],
                    ],
                    [
                        'key' => 'content',
                        'label' => 'Terms & Conditions Content',
                        'type' => 'markdown',
                        'rules' => ['nullable', 'string', 'max:10240'],
                    ],
                    [
                        'key' => 'sidebar_image',
                        'label' => 'Sidebar Image (Optional)',
                        'type' => 'image',
                        'rules' => ['nullable', 'image', 'max:2048'],
                    ],
                ],
                'value' => [
                    'title' => 'Terms & Conditions',
                    'last_updated' => 'December 2025',
                    'content' => "Please read these terms and conditions carefully before using our website.

## Acceptance of Terms

By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.

## Use License

Permission is granted to temporarily download one copy of the materials on ArushiMart's website for personal, non-commercial transitory viewing only.

This is the grant of a license, not a transfer of title, and under this license you may not:

- Modify or copy the materials
- Use the materials for any commercial purpose
- Attempt to decompile or reverse engineer any software contained on the website
- Remove any copyright or other proprietary notations from the materials

## Product Information

We strive to provide accurate product information. However, we do not warrant that product descriptions or other content is accurate, complete, reliable, current, or error-free.

## Pricing

All prices are subject to change without notice. We reserve the right to modify or discontinue products without notice.

## Orders and Payment

By placing an order, you agree to provide current, complete, and accurate purchase and account information. We reserve the right to refuse or cancel any order.

## Shipping and Delivery

Shipping times are estimates and not guaranteed. We are not responsible for delays caused by shipping carriers or customs.

## Returns and Refunds

Please review our Return Policy for detailed information about returns and refunds.

## Limitation of Liability

ArushiMart shall not be liable for any damages arising from the use or inability to use our website or products.

## Contact

For questions about these Terms & Conditions, please contact us at support@arushimart.com.",
                    'sidebar_image' => null,
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
            [
                'key' => 'receipt',
                'label' => 'Receipt',
                'fields' => [
                    [
                        'key' => 'shop_address',
                        'label' => 'Shop Address',
                        'type' => 'text',
                        'rules' => ['required', 'string', 'max:255'],
                    ],
                    [
                        'key' => 'shop_phone',
                        'label' => 'Shop Phone',
                        'type' => 'text',
                        'rules' => ['required', 'string', 'max:50'],
                    ],
                ],
                'value' => [
                    'shop_address' => 'Dhaka, Bangladesh',
                    'shop_phone' => '+880 1234-567890',
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
