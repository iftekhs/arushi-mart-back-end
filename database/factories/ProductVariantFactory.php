<?php

namespace Database\Factories;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'color_id' => Color::inRandomOrder()->first()->id ?? Color::factory(),
            'size_id' => Size::inRandomOrder()->first()->id ?? Size::factory(),
            'sku' => 'VAR-' . strtoupper(Str::random(10)),
            'stock_quantity' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the variant has stock.
     */
    public function inStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'stock_quantity' => fake()->numberBetween(10, 100),
        ]);
    }

    /**
     * Indicate that the variant is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }
}
