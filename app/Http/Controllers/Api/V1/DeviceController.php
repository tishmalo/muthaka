<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:2048',
            'platform' => ['required', 'string', Rule::in(['android', 'ios'])],
            'device_name' => 'nullable|string|max:255',
            'device_id' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:50',
            'os_version' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $this->notifications->registerToken($request->user(), $request->token, $request->platform, $request->only([
            'device_name',
            'device_id',
            'app_version',
            'os_version',
        ]));

        return ApiResponse::success(['devices' => $request->user()->notificationTokens()->latest('last_used_at')->get()], 'Device registered successfully', 201);
    }

    public function unregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required_without:device_id|string|max:2048',
            'device_id' => 'required_without:token|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $query = $request->user()->notificationTokens();

        if ($request->filled('token')) {
            $query->where('token', $request->token);
        } else {
            $query->where('device_id', $request->device_id);
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
