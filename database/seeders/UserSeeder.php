<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 regular users with USER role
        User::factory()->count(50)->create();

        // Optionally create a test user with known credentials
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'role' => UserRole::USER,
            'email_verified_at' => now(),
        ]);
    }
}
