<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@secureshare.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create a manager user
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@secureshare.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MANAGER,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create a member user
        User::create([
            'name' => 'Member User',
            'email' => 'member@secureshare.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
