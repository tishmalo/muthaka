<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => '+254' . $this->faker->unique()->numerify('7#########'),
            'phone_verified_at' => now(),
            'bio' => $this->faker->sentence(10),
            'birthday' => $this->faker->date('Y-m-d', '-18 years'),
            'settings' => [
                'language' => 'en',
                'notifications' => true,
                'theme' => 'light',
            ],
            'status' => 'active',
            'last_active_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone_verified_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}