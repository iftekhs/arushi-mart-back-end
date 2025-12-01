<?php

namespace Database\Seeders;

use App\Models\Customization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customization::create([
            'key' => 'home_page',
            'label' => 'Home Page',
            'value' => null,
        ]);
    }
}
