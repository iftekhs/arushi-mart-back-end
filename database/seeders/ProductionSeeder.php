<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Superadmin',
            'email' => 'arushimart0@gmail.com',
            'role' => UserRole::SUPERADMIN,
            'status' => UserStatus::ACTIVE,
        ]);

        $this->call([
            CustomizationSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
