<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tags = [
            'Trending',
            'Best Seller',
            'New Arrival',
            'Discounted',
            'Premium',
            'Luxury',
            'Comfortable',
            'Elegant',
            'Stylish',
            'Modern',
            'Traditional',
            'Casual',
            'Formal',
            'Festive',
            'Summer Special',
        ];

        $name = fake()->unique()->randomElement($tags);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'active' => true,
        ];
    }
}
