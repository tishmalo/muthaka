<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleTokenVerifier
{
    public function verify(string $idToken): ?array
    {
        try {
            $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $payload = $response->json();

            $expectedClientId = config('services.google.client_id');
            if ($expectedClientId && ($payload['aud'] ?? null) !== $expectedClientId) {
                Log::warning('Google login audience mismatch', ['aud' => $payload['aud'] ?? null]);

                return null;
            }

            if (empty($payload['sub']) || empty($payload['email']) || ($payload['email_verified'] ?? null) !== 'true') {
                return null;
            }

            return $payload;
        } catch (Throwable $e) {
            Log::warning('Google token verification failed', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
