<?php

return [
'connections' => [
    'reverb' => [
        'driver' => 'reverb',
        'key' => env('REVERB_APP_KEY'),
        'secret' => env('REVERB_APP_SECRET'),
        'app_id' => env('REVERB_APP_ID'),
        'options' => [
            'host' => env('REVERB_HOST'),
            'port' => env('REVERB_PORT'),
            'scheme' => env('REVERB_SCHEME'),
            'useTLS' => env('REVERB_SCHEME') === 'https',
        ],
        'client_options' => [
            // Guzzle client options
        ],
    ],
],

];
