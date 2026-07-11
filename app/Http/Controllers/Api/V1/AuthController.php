<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PhoneVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:users,phone_number',
            'email' => 'required|email|unique:users,email',
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

        $this->issueOtp($user->email, 'email_verification');

        return ApiResponse::success([
            'user' => $user,
            'message' => 'Please verify your email with the code sent',
        ], 'Registration successful', 201);
    }

    public function verifyPhone(Request $request)
    {
        return $this->verifyEmail($request);
    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        if (!$this->verifyOtp($request->email, $request->code, 'email_verification')) {
            return ApiResponse::error('Invalid or expired OTP', null, 400);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->forceFill(['email_verified_at' => now()])->save();

        return ApiResponse::success([
            'verified' => true,
            'user' => $user->fresh(),
        ], 'Email verified successfully');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone_number|email',
            'phone_number' => 'required_without:email|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $user = $request->filled('email')
            ? User::where('email', $request->email)->first()
            : User::where('phone_number', $request->phone_number)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->email_verified_at) {
            return ApiResponse::error('Email not verified', null, 403);
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
        return $this->resendEmailOtp($request);
    }

    public function resendEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $this->issueOtp($request->email, 'email_verification');

        return ApiResponse::success(null, 'Email verification code sent successfully');
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $this->issueOtp($request->email, 'password_reset');

        return ApiResponse::success(null, 'Password reset code sent');
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        if (!$this->verifyOtp($request->email, $request->code, 'password_reset')) {
            return ApiResponse::error('Invalid or expired OTP', null, 400);
        }

        $user = User::where('email', $request->email)->firstOrFail();
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

    private function issueOtp(string $identifier, string $type): void
    {
        PhoneVerification::where('phone_number', $identifier)
            ->where('type', $type)
            ->delete();

        $code = app()->environment('testing') ? '123456' : (string) random_int(100000, 999999);

        PhoneVerification::create([
            'phone_number' => $identifier,
            'code' => Hash::make($code),
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->sendOtpEmail($identifier, $code, $type);

        if (!app()->isProduction()) {
            Log::info('OTP issued', [
                'identifier' => $identifier,
                'type' => $type,
                'code' => $code,
            ]);
        }
    }

    private function verifyOtp(string $identifier, string $code, string $type): bool
    {
        $verification = PhoneVerification::where('phone_number', $identifier)
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

    private function sendOtpEmail(string $email, string $code, string $type): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $subject = $type === 'password_reset'
            ? 'Your Muthaka password reset code'
            : 'Your Muthaka email verification code';

        try {
            Mail::raw("Your Muthaka code is {$code}. It expires in 10 minutes.", function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (Throwable $exception) {
            Log::warning('OTP email could not be sent', [
                'email' => $email,
                'type' => $type,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}

