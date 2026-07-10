<?php

namespace Tests\Feature;

use App\Models\Couple;
use App\Models\CoupleInvite;
use App\Models\CoupleUser;
use App\Models\MoodEvent;
use App\Models\NoteEvent;
use App\Models\Prompt;
use App\Models\Snap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MvpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_register_verify_login_profile_and_reset_flow(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Amina',
            'phone_number' => '+254700111222',
            'email' => 'amina@example.com',
            'password' => 'password123',
        ])->assertCreated()->assertJsonPath('success', true);

        $user = User::where('phone_number', '+254700111222')->firstOrFail();
        $verification = '123456';

        $this->postJson('/api/v1/auth/verify-phone', [
            'phone_number' => '+254700111222',
            'code' => $verification,
        ])->assertOk()->assertJsonPath('success', true);

        $this->postJson('/api/v1/auth/login', [
            'phone_number' => '+254700111222',
            'password' => 'password123',
        ])->assertOk()->assertJsonPath('data.token_type', 'Bearer');

        Sanctum::actingAs($user->fresh());
        $this->putJson('/api/v1/auth/profile', ['name' => 'Amina K'])->assertOk();
        $this->postJson('/api/v1/auth/logout')->assertOk();

        $this->postJson('/api/v1/auth/forgot-password', [
            'phone_number' => '+254700111222',
        ])->assertOk();

        $reset = '123456';
        $this->postJson('/api/v1/auth/reset-password', [
            'phone_number' => '+254700111222',
            'code' => $reset,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk();
    }

    public function test_couple_pairing_and_widget_state(): void
    {
        [$user, $partner] = $this->users();
        Sanctum::actingAs($user);

        $invite = $this->postJson('/api/v1/couple/invite', [
            'phone_number' => $partner->phone_number,
        ])->assertCreated()->json('data.invite_code');

        Sanctum::actingAs($partner);
        $this->postJson("/api/v1/couple/accept/{$invite}")->assertOk();
        $this->getJson('/api/v1/couple/status')->assertOk()->assertJsonPath('data.has_couple', true);
        $this->getJson('/api/v1/couple/partner')->assertOk()->assertJsonPath('data.partner.id', $user->id);
        $this->getJson('/api/v1/widget-state')->assertOk()->assertJsonPath('data.widget_state.version', 0);
        $this->getJson('/api/v1/widget-state/version')->assertOk()->assertJsonPath('data.version', 0);
    }

    public function test_moods_notes_countdowns_and_devices_update_state(): void
    {
        [$user] = $this->couple();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/devices/register', [
            'token' => 'token-1',
            'platform' => 'android',
        ])->assertCreated();
        $this->getJson('/api/v1/devices')->assertOk()->assertJsonCount(1, 'data.devices');
        $this->deleteJson('/api/v1/devices/unregister', ['token' => 'token-1'])->assertOk();

        $this->postJson('/api/v1/moods', ['mood_type' => 'happy'])->assertCreated();
        $this->getJson('/api/v1/moods')->assertOk()->assertJsonPath('data.meta.total', 1);
        $this->putJson('/api/v1/moods/mark-seen')->assertOk();

        $noteId = $this->postJson('/api/v1/notes', ['content' => 'Thinking of you'])
            ->assertCreated()
            ->json('data.note.id');
        $this->getJson('/api/v1/notes')->assertOk()->assertJsonPath('data.meta.total', 1);

        $partner = $user->getPartner();
        Sanctum::actingAs($partner);
        $this->getJson('/api/v1/notes/unseen')->assertOk()->assertJsonCount(1, 'data.notes');
        $this->putJson("/api/v1/notes/{$noteId}/seen")->assertOk();

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/countdowns', [
            'event_name' => 'Anniversary',
            'event_date' => now()->addDays(10)->toDateString(),
            'is_active' => true,
        ])->assertCreated();
        $this->getJson('/api/v1/countdowns/active')->assertOk()->assertJsonCount(1, 'data.countdowns');
        $this->getJson('/api/v1/widget-state/version')->assertOk()->assertJsonPath('data.version', 3);
    }

    public function test_media_distance_prompts_and_payments(): void
    {
        Storage::fake('local');
        [$user, $partner] = $this->couple();
        Prompt::create(['content' => 'What made you smile today?', 'type' => 'daily', 'is_active' => true]);

        Sanctum::actingAs($user);
        $doodleId = $this->postJson('/api/v1/doodles', [
            'image' => UploadedFile::fake()->image('doodle.jpg', 32, 32),
        ])->assertCreated()->json('data.doodle.id');
        $this->getJson('/api/v1/doodles')->assertOk()->assertJsonPath('data.meta.total', 1);

        $snapId = $this->postJson('/api/v1/snaps', [
            'image' => UploadedFile::fake()->image('snap.jpg', 32, 32),
            'duration' => 5,
        ])->assertCreated()->json('data.snap.id');
        $this->getJson('/api/v1/snaps')->assertOk()->assertJsonPath('data.meta.total', 1);

        $this->postJson('/api/v1/distance', [
            'latitude' => -1.286389,
            'longitude' => 36.817223,
        ])->assertCreated();
        $this->getJson('/api/v1/distance/history')->assertOk()->assertJsonPath('data.meta.total', 1);

        $promptId = $this->getJson('/api/v1/prompts/daily')->assertOk()->json('data.prompt.id');
        $this->postJson("/api/v1/prompts/{$promptId}/answers", [
            'answer' => 'Your message.',
            'reaction' => 5,
        ])->assertCreated();
        $this->getJson('/api/v1/prompts/history')->assertOk()->assertJsonPath('data.meta.total', 1);

        $this->getJson('/api/v1/subscriptions/current')->assertOk()->assertJsonPath('data.is_premium', false);
        $this->postJson('/api/v1/payments/initiate', ['amount' => 100, 'plan' => 'premium'])
            ->assertCreated()
            ->assertJsonPath('data.payment.status', 'pending');

        Sanctum::actingAs($partner);
        $this->getJson('/api/v1/doodles/unseen')->assertOk()->assertJsonCount(1, 'data.doodles');
        $this->putJson("/api/v1/doodles/{$doodleId}/seen")->assertOk();
        $this->getJson('/api/v1/snaps/unseen')->assertOk()->assertJsonCount(1, 'data.snaps');
        $this->getJson("/api/v1/snaps/{$snapId}/view")->assertOk()->assertJsonPath('data.snap.is_seen', true);
    }

    public function test_expired_snap_is_excluded_and_owned_delete_is_enforced(): void
    {
        Storage::fake('local');
        [$user, $partner, $couple] = $this->couple();
        $expired = Snap::create([
            'couple_id' => $couple->id,
            'sender_id' => $user->id,
            'receiver_id' => $partner->id,
            'image_path' => 'snaps/old.jpg',
            'expires_at' => now()->subHour(),
        ]);

        Sanctum::actingAs($partner);
        $this->getJson('/api/v1/snaps')->assertOk()->assertJsonPath('data.meta.total', 0);
        $this->deleteJson("/api/v1/snaps/{$expired->id}")->assertNotFound();

        Sanctum::actingAs($user);
        $this->deleteJson("/api/v1/snaps/{$expired->id}")->assertOk();
    }

    private function users(): array
    {
        return [
            User::factory()->create(['password' => Hash::make('password')]),
            User::factory()->create(['password' => Hash::make('password')]),
        ];
    }

    private function couple(): array
    {
        [$user, $partner] = $this->users();
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

        return [$user, $partner, $couple];
    }

    private function latestOtp(string $phone, string $type): string
    {
        $verification = \App\Models\PhoneVerification::where('phone_number', $phone)
            ->where('type', $type)
            ->latest('id')
            ->firstOrFail();

        foreach (range(100000, 999999) as $code) {
            if (Hash::check((string) $code, $verification->code)) {
                return (string) $code;
            }
        }

        $this->fail('OTP not found');
    }
}


