<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+254700000000',
            'phone_verified_at' => now(),
            'settings' => ['language' => 'en', 'notifications' => true],
        ]);

        // Create 10 random users
        User::factory(10)->create();
    }
}