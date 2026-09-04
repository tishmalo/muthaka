<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByEmailOrFail(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function findByPhone(string $phone): ?User
    {
        return User::where('phone_number', $phone)->first();
    }

    public function findByGoogleId(string $googleId): ?User
    {
        return User::where('google_id', $googleId)->first();
    }

    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }
}
