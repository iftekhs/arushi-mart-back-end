<?php

namespace Database\Factories;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
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
            'color_id' => $this->faker->boolean(60) ? (Color::inRandomOrder()->first()->id ?? null) : null,
            'path' => 'products/' . $this->faker->uuid() . '.jpg',
            'primary' => false,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }

    /**
     * Indicate that the image is primary.
     */
    public function primary(): static
    {
        return $this->state(fn(array $attributes) => [
            'primary' => true,
            'sort_order' => 0,
        ]);
    }

    /**
     * Indicate that the image is color-specific.
     */
    public function forColor(int $colorId): static
    {
        return $this->state(fn(array $attributes) => [
            'color_id' => $colorId,
        ]);
    }
}
