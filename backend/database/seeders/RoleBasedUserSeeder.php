<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleBasedUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users for Vue frontend
        $users = [
            [
                'first_name' => 'John',
                'last_name' => 'User',
                'name' => 'John User',
                'email' => 'user@demo.com',
                'phone' => '+1234567890',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Driver',
                'name' => 'Jane Driver',
                'email' => 'driver@demo.com',
                'phone' => '+1234567891',
                'password' => Hash::make('password123'),
                'role' => 'driver',
                'driver_license' => 'DL123456789',
                'driver_status' => 'approved',
                'is_active' => true,
            ],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'name' => 'Admin User',
                'email' => 'admin@demo.com',
                'phone' => '+1234567892',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'permissions' => ['manage_users', 'manage_drivers', 'view_analytics'],
                'is_active' => true,
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+1234567893',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Driver',
                'name' => 'Sarah Driver',
                'email' => 'sarah@driver.com',
                'phone' => '+1234567894',
                'password' => Hash::make('password123'),
                'role' => 'driver',
                'driver_license' => 'DL987654321',
                'driver_status' => 'pending',
                'is_active' => true,
            ]
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Demo users created successfully!');
        $this->command->info('User: user@demo.com / password123');
        $this->command->info('Driver: driver@demo.com / password123');
        $this->command->info('Admin: admin@demo.com / password123 (Code: admin123)');
    }
}