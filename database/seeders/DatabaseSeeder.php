<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Superadmin',
            'email' => 'superadmin@email.com',
            'role' => UserRole::SUPERADMIN,
            'status' => UserStatus::ACTIVE,
        ]);

        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'user@email.com',
            'role' => UserRole::USER,
            'status' => UserStatus::ACTIVE,
        ]);

        // Seed products and related data
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
