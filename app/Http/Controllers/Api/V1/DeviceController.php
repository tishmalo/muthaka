<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RegisterDeviceRequest;
use App\Http\Requests\Api\V1\UnregisterDeviceRequest;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function register(RegisterDeviceRequest $request)
    {
        $data = $request->validated();

        $this->notifications->registerToken($request->user(), $data['token'], $data['platform'], array_intersect_key($data, array_flip([
            'device_name',
            'device_id',
            'app_version',
            'os_version',
        ])));

        return ApiResponse::success(['devices' => $request->user()->notificationTokens()->latest('last_used_at')->get()], 'Device registered successfully', 201);
    }

    public function unregister(UnregisterDeviceRequest $request)
    {
        $data = $request->validated();
        $query = $request->user()->notificationTokens();

        if (!empty($data['token'])) {
            $query->where('token', $data['token']);
        } else {
            $query->where('device_id', $data['device_id']);
        }

        if (!$query->update(['is_active' => false])) {
            return ApiResponse::notFound('Device not found');
        }

        return ApiResponse::success(null, 'Device unregistered successfully');
    }

    public function index(Request $request)
    {
        return ApiResponse::success([
            'devices' => $request->user()->notificationTokens()->latest('last_used_at')->get(),
        ]);
    }
}

