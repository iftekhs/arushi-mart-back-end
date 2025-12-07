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
            [
                'name' => 'Small',
                'abbreviation' => 'S',
                'sort_order' => 1
            ],
            [
                'name' => 'Medium',
                'abbreviation' => 'M',
                'sort_order' => 2
            ],
            [
                'name' => 'Large',
                'abbreviation' => 'L',
                'sort_order' => 3
            ],
            [
                'name' => 'Extra Large',
                'abbreviation' => 'XL',
                'sort_order' => 4
            ],
            [
                'name' => '2 Extra Large',
                'abbreviation' => '2XL',
                'sort_order' => 5
            ],
            [
                'name' => '3 Extra Large',
                'abbreviation' => '3XL',
                'sort_order' => 6
            ],
            [
                'name' => '4 Extra Large',
                'abbreviation' => '4XL',
                'sort_order' => 7
            ],
        ];

        $size = fake()->unique()->randomElement($sizes);

        return [
            'name' => $size['name'],
            'sort_order' => $size['sort_order'],
            'abbreviation' => $size['abbreviation'],
        ];
    }
}
