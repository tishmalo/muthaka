<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\NotificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
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

        $token = NotificationToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $request->user()->id,
                'platform' => $request->platform,
                'device_name' => $request->device_name,
                'device_id' => $request->device_id,
                'app_version' => $request->app_version,
                'os_version' => $request->os_version,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return ApiResponse::success(['device' => $token], 'Device registered successfully', 201);
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

        $query = NotificationToken::where('user_id', $request->user()->id);

        if ($request->filled('token')) {
            $query->where('token', $request->token);
        } else {
            $query->where('device_id', $request->device_id);
        }

        $updated = $query->update(['is_active' => false]);

        if (!$updated) {
            return ApiResponse::notFound('Device not found');
        }

        return ApiResponse::success(null, 'Device unregistered successfully');
    }

    public function index(Request $request)
    {
        $devices = NotificationToken::where('user_id', $request->user()->id)
            ->latest('last_used_at')
            ->get();

        return ApiResponse::success(['devices' => $devices]);
    }
}
