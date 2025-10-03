<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@estore.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567890',
            'is_admin' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create regular users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567891',
            'date_of_birth' => '1990-01-15',
            'gender' => 'male',
            'is_admin' => false,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567892',
            'date_of_birth' => '1985-05-20',
            'gender' => 'female',
            'is_admin' => false,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Mike Johnson',
            'email' => 'mike@example.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567893',
            'date_of_birth' => '1992-08-10',
            'gender' => 'male',
            'is_admin' => false,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
