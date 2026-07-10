<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Distance\DistanceService;
use App\Services\Notification\NotificationService;
use App\Services\Support\CoupleContextService;
use App\Services\Widget\WidgetStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MvpServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_distance_calculation_uses_haversine_kilometers(): void
    {
        $service = new DistanceService(
            $this->app->make(CoupleContextService::class),
            $this->app->make(WidgetStateService::class),
        );

        $this->assertEqualsWithDelta(0, $service->calculateDistance(-1.286389, 36.817223, -1.286389, 36.817223), 0.01);
        $this->assertEqualsWithDelta(2.7, $service->calculateDistance(-1.286389, 36.817223, -1.2921, 36.8219), 2.0);
    }

    public function test_fake_notification_driver_records_notification(): void
    {
        $user = User::factory()->create();
        $service = new NotificationService();

        $this->assertTrue($service->sendPush($user, 'Hello', 'Body', ['type' => 'test']));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Hello',
        ]);
    }
}
