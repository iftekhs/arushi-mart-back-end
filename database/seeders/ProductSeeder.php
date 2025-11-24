<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Seed Categories (5 total, 2 with parents)
            $this->seedCategories();

            // 2. Seed Colors (10 colors with realistic names and hex codes)
            $this->seedColors();

            // 3. Seed Sizes (7 sizes: S, M, L, XL, 2XL, 3XL, 4XL)
            $this->seedSizes();

            // 4. Seed Tags (15 tags)
            $this->seedTags();

            // 5. Seed Products with all relationships
            $this->seedProducts();
        });

        $this->command->info('Product seeding completed successfully!');
    }

    private function seedCategories(): void
    {
        $categories = [
            [
                'name' => 'Women\'s Clothing',
                'slug' => 'womens-clothing',
                'description' => 'Explore our exclusive collection of women\'s clothing',
                'image' => 'https://jazmin.pk/cdn/shop/files/Semi_Formals_Web_Tiles_02.jpg?v=1759151385&width=500',
                'video' => 'https://jazmin.pk/cdn/shop/videos/c/vp/6b10bf4a7b9c4493a6761865d0b9de2b/6b10bf4a7b9c4493a6761865d0b9de2b.HD-1080p-2.5Mbps-45995465.mp4?v=0',
                'active' => true,
                'featured' => true,
                'showcased' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Men\'s Clothing',
                'slug' => 'mens-clothing',
                'description' => 'Premium quality men\'s fashion collection',
                'image' => 'https://jazmin.pk/cdn/shop/files/RTW_Winters_Web_Tiles.jpg?v=1759151374&width=500',
                'video' => 'https://jazmin.pk/cdn/shop/videos/c/vp/3e444bc84a4042d3a8a51ea60a62e2ae/3e444bc84a4042d3a8a51ea60a62e2ae.HD-1080p-7.2Mbps-60219227.mp4?v=0',
                'active' => true,
                'featured' => true,
                'showcased' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Traditional Wear',
                'slug' => 'traditional-wear',
                'description' => 'Traditional and ethnic wear for all occasions',
                'image' => 'https://jazmin.pk/cdn/shop/files/UW_Tile.jpg?v=1759584304&width=500',
                'video' => 'https://jazmin.pk/cdn/shop/videos/c/vp/813461518f154a84a33160d45955e7b5/813461518f154a84a33160d45955e7b5.HD-1080p-7.2Mbps-58137508.mp4?v=0',
                'active' => true,
                'featured' => true,
                'showcased' => true,
                'parent_id' => null,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create 2 child categories
        Category::create([
            'name' => 'Women\'s Traditional',
            'slug' => 'womens-traditional',
            'description' => 'Traditional wear for women',
            'active' => true,
            'parent_id' => 1, // Women's Clothing
        ]);

        Category::create([
            'name' => 'Men\'s Casual',
            'slug' => 'mens-casual',
            'description' => 'Casual wear for men',
            'active' => true,
            'parent_id' => 2, // Men's Clothing
        ]);

        $this->command->info('✓ Created 5 categories (3 parent, 2 child)');
    }

    private function seedColors(): void
    {
        $colors = [
            ['name' => 'Black', 'hex_code' => '#000000'],
            ['name' => 'White', 'hex_code' => '#FFFFFF'],
            ['name' => 'Red', 'hex_code' => '#DC143C'],
            ['name' => 'Navy Blue', 'hex_code' => '#000080'],
            ['name' => 'Emerald Green', 'hex_code' => '#50C878'],
            ['name' => 'Maroon', 'hex_code' => '#800000'],
            ['name' => 'Beige', 'hex_code' => '#F5F5DC'],
            ['name' => 'Royal Blue', 'hex_code' => '#4169E1'],
            ['name' => 'Mustard', 'hex_code' => '#FFDB58'],
            ['name' => 'Pink', 'hex_code' => '#FFC0CB'],
        ];

        foreach ($colors as $color) {
            Color::create([
                'name' => $color['name'],
                'hex_code' => $color['hex_code'],
                'active' => true,
            ]);
        }

        $this->command->info('✓ Created 10 colors');
    }

    private function seedSizes(): void
    {
        $sizes = [
            ['name' => 'S', 'sort_order' => 1],
            ['name' => 'M', 'sort_order' => 2],
            ['name' => 'L', 'sort_order' => 3],
            ['name' => 'XL', 'sort_order' => 4],
            ['name' => '2XL', 'sort_order' => 5],
            ['name' => '3XL', 'sort_order' => 6],
            ['name' => '4XL', 'sort_order' => 7],
        ];

        foreach ($sizes as $size) {
            Size::create([
                'name' => $size['name'],
                'sort_order' => $size['sort_order'],
                'active' => true,
            ]);
        }

        $this->command->info('✓ Created 7 sizes');
    }

    private function seedTags(): void
    {
        $tags = [
            'Trending',
            'Best Seller',
            'New Arrival',
            'On Sale',
            'Premium Quality',
            'Limited Edition',
            'Comfortable',
            'Elegant',
            'Stylish',
            'Modern',
            'Traditional',
            'Summer Collection',
            'Winter Collection',
            'Festive Wear',
            'Party Wear',
        ];

        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag,
                'slug' => Str::slug($tag),
                'active' => true,
            ]);
        }

        $this->command->info('✓ Created 15 tags');
    }

    private function seedProducts(): void
    {
        $products = [
            [
                'name' => 'Embroidered Lawn 3-Piece Suit',
                'price' => 4500.00,
                'description' => 'Elegant embroidered lawn suit perfect for summer occasions. Features intricate embroidery work on pure lawn fabric.',
                'featured' => true,
                'images' =>  [
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSggVYHPc6ECK1IZsAkmcXTMp75t4qx9SvzYn2',
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSTalUJhKG9qUStJy4lZkNPraMuzv6xKLCgjQ5'
                ],
            ],
            [
                'name' => 'Premium Cotton Kurta',
                'price' => 2800.00,
                'description' => 'Comfortable cotton kurta with modern cut and traditional appeal. Ideal for casual and semi-formal events.',
                'featured' => true,
                'images' =>  [
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSrTzzLN7qYpjwgMK6UnovOs2xaW8T4X5hJ17k',
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUShnzEjQzYKjUInM2A76owsJ5NXgpOPRBECaSv'
                ],
            ],
            [
                'name' => 'Silk Dupatta Collection',
                'price' => 3200.00,
                'description' => 'Pure silk dupatta with beautiful patterns and premium quality fabric. Perfect complement to any outfit.',
                'featured' => true,
                'images' =>  [
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSru6pTI7qYpjwgMK6UnovOs2xaW8T4X5hJ17k',
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSXhmKndxhYb0tBSNd5i7jepoJwuzvALafsnOI'
                ],
            ],
            [
                'name' => 'Formal Blazer Premium',
                'price' => 8900.00,
                'description' => 'High-quality formal blazer with modern fit. Perfect for business meetings and formal occasions.',
                'featured' => true,
                'images' => [
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSQOK4GwzwqGf1nTu9D2YpdJxygcstAaK6jlWN',
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSz0rXk53yhYxUnpoqZ1FdwIt3fKXC8POl2WNR'
                ],
            ],
            [
                'name' => 'Chiffon Party Dress',
                'price' => 6500.00,
                'description' => 'Stunning chiffon dress with elegant design. Perfect for evening parties and special occasions.',
                'featured' => true,
                'images' =>  [
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUS3ivpBYSbsKJSkBDyucTpN4RemIQC7EG9AxfL',
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSlCwox6p81Qf9XSI4tj5PqnMECNcxWoskRTUu'
                ]
            ],
            [
                'name' => 'Traditional Kameez Shalwar',
                'price' => 3500.00,
                'description' => 'Classic traditional wear with comfortable fit and beautiful embroidery. Suitable for all occasions.',
                'images' =>  [
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSQOK4GwzwqGf1nTu9D2YpdJxygcstAaK6jlWN',
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSz0rXk53yhYxUnpoqZ1FdwIt3fKXC8POl2WNR'
                ]
            ],
            [
                'name' => 'Linen Casual Shirt',
                'price' => 2200.00,
                'description' => 'Breathable linen shirt perfect for hot summer days. Comfortable and stylish.',
                'images' =>  [
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUS3ivpBYSbsKJSkBDyucTpN4RemIQC7EG9AxfL',
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSlCwox6p81Qf9XSI4tj5PqnMECNcxWoskRTUu'
                ]
            ],
            [
                'name' => 'Velvet Winter Shawl',
                'price' => 5500.00,
                'description' => 'Luxurious velvet shawl to keep you warm in style. Premium quality fabric with elegant design.',
                'images' =>  [
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSQOK4GwzwqGf1nTu9D2YpdJxygcstAaK6jlWN',
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSz0rXk53yhYxUnpoqZ1FdwIt3fKXC8POl2WNR'
                ]
            ],
            [
                'name' => 'Denim Jacket Collection',
                'price' => 4800.00,
                'description' => 'Trendy denim jacket with modern cuts. Perfect for casual outings and streetwear style.',
                'images' =>  [
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUS3ivpBYSbsKJSkBDyucTpN4RemIQC7EG9AxfL',
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSlCwox6p81Qf9XSI4tj5PqnMECNcxWoskRTUu'
                ]
            ],
            [
                'name' => 'Designer Gown Special',
                'price' => 12000.00,
                'description' => 'Exclusive designer gown with intricate detailing. Perfect for weddings and grand celebrations.',
                'images' =>  [
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSQOK4GwzwqGf1nTu9D2YpdJxygcstAaK6jlWN',
                    'https://ucgljov7fm.ufs.sh/f/T0RTWqKG9qUSz0rXk53yhYxUnpoqZ1FdwIt3fKXC8POl2WNR'
                ]
            ],
        ];

        $categories = Category::all();
        $colors = Color::all();
        $sizes = Size::all();
        $tags = Tag::all();
        $types = [ProductType::STITCHED->value, ProductType::UNSTITCHED->value];

        foreach ($products as $index => $productData) {
            // Choose 2-4 random categories for this product and pick one as default
            $randomCategories = $categories->random(rand(2, 4));
            $defaultCategoryId = $randomCategories->random()->id;

            // Create product with default category_id
            $product = Product::create([
                'sku' => 'PRD-' . strtoupper(Str::random(8)),
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']) . '-' . ($index + 1),
                'price' => $productData['price'],
                'description' => $productData['description'],
                'active' => true,
                'featured' => $productData['featured'] ?? false,
                'category_id' => $defaultCategoryId,
            ]);

            // Attach the chosen categories
            $product->categories()->attach($randomCategories->pluck('id'));

            // Attach 2-5 random tags
            $randomTags = $tags->random(rand(2, 5));
            $product->tags()->attach($randomTags->pluck('id'));

            // Select 3-4 random colors for this product
            $productColors = $colors->random(rand(3, 4));
            $colorId = $productColors->random()->id;

            foreach ($productData['images'] as $i => $imageUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'color_id' => $colorId,
                    'path' => $imageUrl,
                    'primary' => $i === 0, // First image is primary
                    'sort_order' => $i,
                ]);
            }

            // Create 6-12 variants per product
            $variantCount = rand(6, 12);
            $createdVariants = 0;
            $variantCombinations = [];

            while ($createdVariants < $variantCount) {
                $color = $productColors->random();
                $type = $types[array_rand($types)];

                // Unstitched products don't have sizes
                $size = $type === ProductType::STITCHED->value ? $sizes->random() : null;
                $sizeId = $size ? $size->id : null;

                // Create unique combination key
                $combinationKey = $color->id . '-' . $sizeId . '-' . $type;

                // Skip if combination already exists
                if (in_array($combinationKey, $variantCombinations)) {
                    continue;
                }

                $variantCombinations[] = $combinationKey;

                // Ensure at least one variant has stock
                $stockQuantity = ($createdVariants === 0)
                    ? rand(20, 100)
                    : rand(0, 80);

                ProductVariant::create([
                    'product_id' => $product->id,
                    'color_id' => $color->id,
                    'size_id' => $sizeId,
                    'type' => $type,
                    'sku' => 'VAR-' . strtoupper(Str::random(10)),
                    'stock_quantity' => $stockQuantity,
                ]);

                $createdVariants++;
            }

            $this->command->info("✓ Created product: {$product->name} with {$variantCount} variants and images");
        }

        $this->command->info('✓ Created 10 products with all relationships');
    }
}
