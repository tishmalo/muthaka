<?php

namespace Tests\Feature;

use App\Models\Couple;
use App\Models\CoupleUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PoisonedWidgetCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_poisoned_widget_cache_entry_is_rebuilt_instead_of_500(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $partner = User::factory()->create(['password' => Hash::make('password')]);
        $couple = Couple::create([
            'partner_one_id' => $user->id,
            'partner_two_id' => $partner->id,
            'status' => 'active',
            'connected_at' => now(),
        ]);

        foreach ([$user, $partner] as $member) {
            CoupleUser::create([
                'couple_id' => $couple->id,
                'user_id' => $member->id,
                'role' => 'partner',
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        // Simulate the production poisoned entry (unserializes to garbage).
        Cache::put("widget_state:{$couple->id}:{$user->id}", new \__PHP_Incomplete_Class, now()->addMinutes(5));

        Sanctum::actingAs($user);
        $this->getJson('/api/v1/widget-state')
            ->assertOk()
            ->assertJsonPath('data.widget_state.user_id', $user->id);
    }
}
