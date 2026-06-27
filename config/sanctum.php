<?php

use App\Models\User;

return [

'stateful' => explode(',', env('SANCTUM_STATEFUL', 'api')),

'expiration' => env('SANCTUM_EXPIRATION', 525600), // 1 year in minutes

'middleware' => [
    // 'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
    'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
    'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
],

];

