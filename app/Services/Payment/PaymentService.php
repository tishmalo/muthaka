<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Str;

class PaymentService
{
    public function currentSubscription(User $user): ?Subscription
    {
        return $user->activeSubscription()->first();
    }

    public function initiate(User $user, array $data): Payment
    {
        return Payment::create([
            'user_id' => $user->id,
            'transaction_id' => 'fake_'.Str::uuid()->toString(),
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'KES',
            'status' => 'pending',
            'provider' => config('tuko.payments.provider', 'fake'),
            'provider_reference' => null,
            'payment_method' => $data['payment_method'] ?? 'fake',
            'description' => $data['description'] ?? 'Subscription payment',
            'metadata' => [
                'plan' => $data['plan'] ?? 'premium',
                'phone_number' => $data['phone_number'] ?? $user->phone_number,
                'fake_provider' => true,
            ],
        ]);
    }
}
