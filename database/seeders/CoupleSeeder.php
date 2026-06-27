<?php

namespace Database\Seeders;

use App\Models\Couple;
use App\Models\CoupleUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoupleSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test couple
        $user1 = User::factory()->create([
            'phone_number' => '+254700000001',
            'email' => 'alice@example.com',
            'name' => 'Alice',
        ]);

        $user2 = User::factory()->create([
            'phone_number' => '+254700000002',
            'email' => 'bob@example.com',
            'name' => 'Bob',
        ]);

        $couple = Couple::create([
            'partner_one_id' => $user1->id,
            'partner_two_id' => $user2->id,
            'status' => 'active',
            'connected_at' => now(),
        ]);

        CoupleUser::create([
            'couple_id' => $couple->id,
            'user_id' => $user1->id,
            'role' => 'partner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        CoupleUser::create([
            'couple_id' => $couple->id,
            'user_id' => $user2->id,
            'role' => 'partner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->command->info('Test couple created successfully!');
    }
}