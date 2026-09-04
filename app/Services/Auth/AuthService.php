<?php

namespace App\Services\Auth;

use App\Contracts\Repositories\OtpRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly OtpRepositoryInterface $otps,
        private readonly GoogleTokenVerifier $google,
    ) {}

    public function register(array $data): User
    {
        $user = $this->users->create([
            'name' => $data['name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $this->issueOtp($user->email, 'email_verification');

        return $user;
    }

    public function verifyEmail(string $email, string $code): User
    {
        if (! $this->verifyOtp($email, $code, 'email_verification')) {
            throw new HttpException(400, 'Invalid or expired OTP');
        }

        $user = $this->users->findByEmailOrFail($email);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->fresh();
    }

    public function login(?string $email, ?string $phone, string $password, ?string $deviceName): array
    {
        $user = isset($email)
            ? $this->users->findByEmail($email)
            : $this->users->findByPhone((string) $phone);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->email_verified_at) {
            throw new HttpException(403, 'Email not verified');
        }

        if ($user->status !== 'active') {
            throw new HttpException(403, 'Account is not active');
        }

        $token = $user->createToken($deviceName ?? 'mobile-app')->plainTextToken;
        $user->update(['last_active_at' => now()]);

        return [$user->fresh(), $token];
    }

    public function loginWithGoogle(string $idToken, ?string $phoneNumber, ?string $deviceName): array
    {
        $google = $this->google->verify($idToken);

        if (! $google) {
            throw new HttpException(401, 'Invalid Google sign-in. Please try again.');
        }

        $user = $this->users->findByGoogleId($google['sub']);

        if (! $user && ! empty($google['email'])) {
            $user = $this->users->findByEmail($google['email']);

            if ($user) {
                $user->forceFill([
                    'google_id' => $google['sub'],
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'avatar' => $user->avatar ?? ($google['picture'] ?? null),
                ])->save();
            }
        }

        if (! $user) {
            $user = $this->users->create([
                'name' => $google['name'] ?? explode('@', $google['email'])[0],
                'email' => $google['email'],
                'phone_number' => $phoneNumber ?? ('google:'.$google['sub']),
                'password' => Hash::make(Str::random(40)),
                'google_id' => $google['sub'],
                'avatar' => $google['picture'] ?? null,
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
        }

        if ($user->status !== 'active') {
            throw new HttpException(403, 'Account is not active');
        }

        $token = $user->createToken($deviceName ?? 'google-app')->plainTextToken;
        $user->update(['last_active_at' => now()]);

        return [$user->fresh(), $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function profile(User $user): User
    {
        return $user->load('activeCouple');
    }

    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        if ($avatar) {
            $data['avatar'] = $avatar->store('avatars', 'public');
        }

        return $this->users->update($user, $data);
    }

    public function resendEmailOtp(string $email): void
    {
        $this->issueOtp($email, 'email_verification');
    }

    public function forgotPassword(string $email): void
    {
        $this->issueOtp($email, 'password_reset');
    }

    public function resetPassword(string $email, string $code, string $password): void
    {
        if (! $this->verifyOtp($email, $code, 'password_reset')) {
            throw new HttpException(400, 'Invalid or expired OTP');
        }

        $user = $this->users->findByEmailOrFail($email);
        $user->forceFill(['password' => Hash::make($password)])->save();
        $user->tokens()->delete();
    }

    public function refresh(User $user): string
    {
        $user->currentAccessToken()?->delete();

        return $user->createToken('mobile-app')->plainTextToken;
    }

    private function issueOtp(string $identifier, string $type): void
    {
        $this->otps->deleteFor($identifier, $type);

        $code = app()->environment('testing') ? '123456' : (string) random_int(100000, 999999);

        $this->otps->create([
            'phone_number' => $identifier,
            'code' => Hash::make($code),
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->sendOtpEmail($identifier, $code, $type);

        if (! app()->isProduction()) {
            Log::info('OTP issued', ['identifier' => $identifier, 'type' => $type, 'code' => $code]);
        }
    }

    private function verifyOtp(string $identifier, string $code, string $type): bool
    {
        $verification = $this->otps->latestValid($identifier, $type);

        if (! $verification || ! Hash::check($code, $verification->code)) {
            return false;
        }

        $this->otps->delete($verification);

        return true;
    }

    private function sendOtpEmail(string $email, string $code, string $type): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
