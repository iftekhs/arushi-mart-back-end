<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Superadmin',
            'email' => 'arushimart0@gmail.com',
            'role' => UserRole::SUPERADMIN,
            'status' => UserStatus::ACTIVE,
            'password' => Hash::make('arushimart0@gmail.com')
        ]);

        $this->call([
            CustomizationSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
