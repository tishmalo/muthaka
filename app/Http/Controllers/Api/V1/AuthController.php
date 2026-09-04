<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\AuthServiceInterface;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\GoogleLoginRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\ResendEmailOtpRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Requests\Api\V1\VerifyEmailRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthServiceInterface $auth)
    {
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->auth->register($request->validated());

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
        try {
            $data = $request->validated();
            $user = $this->auth->verifyEmail($data['email'], $data['code']);

            return ApiResponse::success(['verified' => true, 'user' => $user], 'Email verified successfully');
        } catch (HttpException $e) {
            return ApiResponse::error($e->getMessage(), null, $e->getStatusCode());
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $data = $request->validated();
            [$user, $token] = $this->auth->login(
                $data['email'] ?? null,
                $data['phone_number'] ?? null,
                $data['password'],
                $data['device_name'] ?? null,
            );

            return ApiResponse::success([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Login successful');
        } catch (HttpException $e) {
            return ApiResponse::error($e->getMessage(), null, $e->getStatusCode());
        }
    }

    public function loginWithGoogle(GoogleLoginRequest $request)
    {
        try {
            $data = $request->validated();
            [$user, $token] = $this->auth->loginWithGoogle(
                $data['id_token'],
                $data['phone_number'] ?? null,
                $data['device_name'] ?? null,
            );

            return ApiResponse::success([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Google login successful');
        } catch (HttpException $e) {
            return ApiResponse::error($e->getMessage(), null, $e->getStatusCode());
        }
    }

    public function logout(Request $request)
    {
        $this->auth->logout($request->user());

        return ApiResponse::success(null, 'Logged out successfully');
    }

    public function me(Request $request)
    {
        return ApiResponse::success([
            'user' => $this->auth->profile($request->user()),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $this->auth->updateProfile(
            $request->user(),
            $request->validated(),
            $request->hasFile('avatar') ? $request->file('avatar') : null,
        );

        return ApiResponse::success(['user' => $user], 'Profile updated successfully');
    }

    public function resendOtp(ResendEmailOtpRequest $request)
    {
        return $this->resendEmailOtp($request);
    }

    public function resendEmailOtp(ResendEmailOtpRequest $request)
    {
        $this->auth->resendEmailOtp($request->validated('email'));

        return ApiResponse::success(null, 'Email verification code sent successfully');
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $this->auth->forgotPassword($request->validated('email'));

        return ApiResponse::success(null, 'Password reset code sent');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $data = $request->validated();
            $this->auth->resetPassword($data['email'], $data['code'], $data['password']);

            return ApiResponse::success(null, 'Password reset successfully');
        } catch (HttpException $e) {
            return ApiResponse::error($e->getMessage(), null, $e->getStatusCode());
        }
    }

    public function refresh(Request $request)
    {
        return ApiResponse::success([
            'token' => $this->auth->refresh($request->user()),
            'token_type' => 'Bearer',
        ], 'Token refreshed');
    }
}
