<?php

return [
    'notifications' => [
        'driver' => env('TUKO_NOTIFICATION_DRIVER', 'fake'),
    ],

    'payments' => [
        'provider' => env('TUKO_PAYMENT_PROVIDER', 'fake'),
    ],

    'media' => [
        'max_upload_kb' => (int) env('TUKO_MEDIA_MAX_UPLOAD_KB', 5120),
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'snap_expiry_hours' => (int) env('TUKO_SNAP_EXPIRY_HOURS', 24),
    ],
];
