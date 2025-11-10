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
    /**
     * Run the database seeds.
     */
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
                'active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Men\'s Clothing',
                'slug' => 'mens-clothing',
                'description' => 'Premium quality men\'s fashion collection',
                'active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Traditional Wear',
                'slug' => 'traditional-wear',
                'description' => 'Traditional and ethnic wear for all occasions',
                'active' => true,
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
            ],
            [
                'name' => 'Premium Cotton Kurta',
                'price' => 2800.00,
                'description' => 'Comfortable cotton kurta with modern cut and traditional appeal. Ideal for casual and semi-formal events.',
            ],
            [
                'name' => 'Silk Dupatta Collection',
                'price' => 3200.00,
                'description' => 'Pure silk dupatta with beautiful patterns and premium quality fabric. Perfect complement to any outfit.',
            ],
            [
                'name' => 'Formal Blazer Premium',
                'price' => 8900.00,
                'description' => 'High-quality formal blazer with modern fit. Perfect for business meetings and formal occasions.',
            ],
            [
                'name' => 'Chiffon Party Dress',
                'price' => 6500.00,
                'description' => 'Stunning chiffon dress with elegant design. Perfect for evening parties and special occasions.',
            ],
            [
                'name' => 'Traditional Kameez Shalwar',
                'price' => 3500.00,
                'description' => 'Classic traditional wear with comfortable fit and beautiful embroidery. Suitable for all occasions.',
            ],
            [
                'name' => 'Linen Casual Shirt',
                'price' => 2200.00,
                'description' => 'Breathable linen shirt perfect for hot summer days. Comfortable and stylish.',
            ],
            [
                'name' => 'Velvet Winter Shawl',
                'price' => 5500.00,
                'description' => 'Luxurious velvet shawl to keep you warm in style. Premium quality fabric with elegant design.',
            ],
            [
                'name' => 'Denim Jacket Collection',
                'price' => 4800.00,
                'description' => 'Trendy denim jacket with modern cuts. Perfect for casual outings and streetwear style.',
            ],
            [
                'name' => 'Designer Gown Special',
                'price' => 12000.00,
                'description' => 'Exclusive designer gown with intricate detailing. Perfect for weddings and grand celebrations.',
            ],
        ];

        $categories = Category::all();
        $colors = Color::all();
        $sizes = Size::all();
        $tags = Tag::all();
        $types = [ProductType::STITCHED->value, ProductType::UNSTITCHED->value];

        foreach ($products as $index => $productData) {
            // Create product
            $product = Product::create([
                'sku' => 'PRD-' . strtoupper(Str::random(8)),
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']) . '-' . ($index + 1),
                'price' => $productData['price'],
                'description' => $productData['description'],
                'active' => true,
            ]);

            // Attach 2-4 random categories
            $randomCategories = $categories->random(rand(2, 4));
            $product->categories()->attach($randomCategories->pluck('id'));

            // Attach 2-5 random tags
            $randomTags = $tags->random(rand(2, 5));
            $product->tags()->attach($randomTags->pluck('id'));

            // Select 3-4 random colors for this product
            $productColors = $colors->random(rand(3, 4));

            // Create 3-5 images per product
            $imageCount = rand(3, 5);
            for ($i = 0; $i < $imageCount; $i++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'color_id' => $i > 0 && rand(0, 1) ? $productColors->random()->id : null,
                    'path' => 'products/' . Str::slug($product->name) . '-' . ($i + 1) . '.jpg',
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
                $size = $sizes->random();
                $type = $types[array_rand($types)];

                // Create unique combination key
                $combinationKey = $color->id . '-' . $size->id . '-' . $type;

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
                    'size_id' => $size->id,
                    'type' => $type,
                    'sku' => 'VAR-' . strtoupper(Str::random(10)),
                    'stock_quantity' => $stockQuantity,
                ]);

                $createdVariants++;
            }

            $this->command->info("✓ Created product: {$product->name} with {$variantCount} variants and {$imageCount} images");
        }

        $this->command->info('✓ Created 10 products with all relationships');
    }
}
