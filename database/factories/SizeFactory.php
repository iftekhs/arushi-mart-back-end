<?php

namespace Database\Factories;

use App\Models\Size;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Size>
 */
class SizeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
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

        $size = fake()->unique()->randomElement($sizes);

        return [
            'name' => $size['name'],
            'sort_order' => $size['sort_order'],
            'active' => true,
        ];
    }
}
