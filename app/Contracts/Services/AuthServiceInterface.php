<?php

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;

interface AuthServiceInterface
{
    public function register(array $data): User;
    public function verifyEmail(string $email, string $code): User;
    public function login(?string $email, ?string $phone, string $password, ?string $deviceName): array;
    public function loginWithGoogle(string $idToken, ?string $phoneNumber, ?string $deviceName): array;
    public function logout(User $user): void;
    public function profile(User $user): User;
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): User;
    public function resendEmailOtp(string $email): void;
    public function forgotPassword(string $email): void;
    public function resetPassword(string $email, string $code, string $password): void;
    public function refresh(User $user): string;
}
