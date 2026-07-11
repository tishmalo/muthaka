<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\ResendEmailOtpRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Requests\Api\V1\VerifyEmailRequest;
use App\Models\PhoneVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $this->issueOtp($user->email, 'email_verification');

        return ApiResponse::success([
            'user' => $user,
            'message' => 'Please verify your email with the code sent',
        ], 'Registration successful', 201);
    }

    public function verifyPhone(VerifyEmailRequest $request)
    {
        return $this->verifyEmail($request);
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        $data = $request->validated();

        if (!$this->verifyOtp($data['email'], $data['code'], 'email_verification')) {
            return ApiResponse::error('Invalid or expired OTP', null, 400);
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->forceFill(['email_verified_at' => now()])->save();

        return ApiResponse::success([
            'verified' => true,
            'user' => $user->fresh(),
        ], 'Email verified successfully');
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = isset($data['email'])
            ? User::where('email', $data['email'])->first()
            : User::where('phone_number', $data['phone_number'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
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

        $token = $user->createToken($data['device_name'] ?? 'mobile-app')->plainTextToken;
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

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return ApiResponse::success([
            'user' => $user->fresh(),
        ], 'Profile updated successfully');
    }

    public function resendOtp(ResendEmailOtpRequest $request)
    {
        return $this->resendEmailOtp($request);
    }

    public function resendEmailOtp(ResendEmailOtpRequest $request)
    {
        $data = $request->validated();
        $this->issueOtp($data['email'], 'email_verification');

        return ApiResponse::success(null, 'Email verification code sent successfully');
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $data = $request->validated();
        $this->issueOtp($data['email'], 'password_reset');

        return ApiResponse::success(null, 'Password reset code sent');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        if (!$this->verifyOtp($data['email'], $data['code'], 'password_reset')) {
            return ApiResponse::error('Invalid or expired OTP', null, 400);
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->forceFill(['password' => Hash::make($data['password'])])->save();
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

