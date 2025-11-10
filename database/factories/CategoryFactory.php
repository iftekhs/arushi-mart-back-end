<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Women\'s Clothing',
            'Men\'s Clothing',
            'Kids Clothing',
            'Traditional Wear',
            'Casual Wear',
            'Formal Wear',
            'Party Wear',
            'Summer Collection',
            'Winter Collection',
            'Accessories',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'parent_id' => null,
            'description' => fake()->sentence(15),
            'active' => true,
        ];
    }

    /**
     * Indicate that the category has a parent.
     */
    public function withParent(int $parentId): static
    {
        return $this->state(fn(array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }
}
