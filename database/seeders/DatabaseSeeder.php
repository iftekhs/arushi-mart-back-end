<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'email' => 'arushimart0@gmail.com',
            'role' => UserRole::SUPERADMIN,
            'status' => UserStatus::ACTIVE,
            'password' => Hash::make('password')
        ]);

        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'user@email.com',
            'role' => UserRole::USER,
            'status' => UserStatus::ACTIVE,
        ]);

        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            CustomizationSeeder::class,
            SettingSeeder::class
        ]);
    }
}
