<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PhoneVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:users,phone_number',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $this->issueOtp($user->phone_number, 'verification');

        return ApiResponse::success([
            'user' => $user,
            'message' => 'Please verify your phone number with the OTP sent',
        ], 'Registration successful', 201);
    }

    public function verifyPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:users,phone_number',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        if (!$this->verifyOtp($request->phone_number, $request->code, 'verification')) {
            return ApiResponse::error('Invalid or expired OTP', null, 400);
        }

        $user = User::where('phone_number', $request->phone_number)->firstOrFail();
        $user->forceFill(['phone_verified_at' => now()])->save();

        return ApiResponse::success([
            'verified' => true,
            'user' => $user->fresh(),
        ], 'Phone verified successfully');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone_number' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->phone_verified_at) {
            return ApiResponse::error('Phone not verified', null, 403);
        }

        if ($user->status !== 'active') {
            return ApiResponse::error('Account is not active', null, 403);
        }

        $token = $user->createToken($request->device_name ?? 'mobile-app')->plainTextToken;
        $user->update(['last_active_at' => now()]);

        return ApiResponse::success([
            'user' => $user->fresh(),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    public function me(Request $request)
    {
        return ApiResponse::success([
            'user' => $request->user()->load('activeCouple'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'bio' => 'nullable|string|max:500',
            'birthday' => 'nullable|date',
            'settings' => 'sometimes|array',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $data = $request->only(['name', 'email', 'bio', 'birthday', 'settings']);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return ApiResponse::success([
            'user' => $user->fresh(),
        ], 'Profile updated successfully');
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:users,phone_number',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $this->issueOtp($request->phone_number, 'verification');

        return ApiResponse::success(null, 'OTP sent successfully');
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:users,phone_number',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $this->issueOtp($request->phone_number, 'password_reset');

        return ApiResponse::success(null, 'Password reset OTP sent');
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:users,phone_number',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        if (!$this->verifyOtp($request->phone_number, $request->code, 'password_reset')) {
            return ApiResponse::error('Invalid or expired OTP', null, 400);
        }

        $user = User::where('phone_number', $request->phone_number)->firstOrFail();
        $user->forceFill(['password' => Hash::make($request->password)])->save();
        $user->tokens()->delete();

        return ApiResponse::success(null, 'Password reset successfully');
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        $request->user()->currentAccessToken()?->delete();
        $token = $user->createToken('mobile-app')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Token refreshed');
    }

    private function issueOtp(string $phoneNumber, string $type): void
    {
        PhoneVerification::where('phone_number', $phoneNumber)
            ->where('type', $type)
            ->delete();

        $code = (string) random_int(100000, 999999);

        PhoneVerification::create([
            'phone_number' => $phoneNumber,
            'code' => Hash::make($code),
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
        ]);

        if (!app()->isProduction()) {
            Log::info('OTP issued', [
                'phone_number' => $phoneNumber,
                'type' => $type,
                'code' => $code,
            ]);
        }
    }

    private function verifyOtp(string $phoneNumber, string $code, string $type): bool
    {
        $verification = PhoneVerification::where('phone_number', $phoneNumber)
            ->where('type', $type)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$verification || !Hash::check($code, $verification->code)) {
            return false;
        }

        $verification->delete();

        return true;
    }
}
