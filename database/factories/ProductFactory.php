<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productNames = [
            'Embroidered Lawn Suit',
            'Cotton Kurta',
            'Silk Dupatta Set',
            'Printed Shirt',
            'Chiffon Dress',
            'Linen Trousers',
            'Velvet Shawl',
            'Denim Jacket',
            'Formal Blazer',
            'Casual T-Shirt',
            'Summer Tunic',
            'Winter Coat',
            'Party Gown',
            'Traditional Shalwar Kameez',
            'Modern Kurta Set',
        ];

        $name = fake()->unique()->randomElement($productNames);
        $sku = 'PRD-' . strtoupper(Str::random(8));

        return [
            'sku' => $sku,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . strtolower(Str::random(4)),
            'price' => fake()->randomFloat(2, 1500, 15000),
            'description' => fake()->paragraph(4),
            'active' => true,
            'category_id' => Category::factory(),
        ];
    }
}
